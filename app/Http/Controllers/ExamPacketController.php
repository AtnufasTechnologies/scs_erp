<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\ExamAttendance;
use App\Models\ExamSystem\ExamDummyNumber;
use App\Models\ExamSystem\ExamPacket;
use App\Models\ExamSystem\ExamPacketStudent;
use App\Models\ExamSystem\ExamSession;
use App\Models\ExamSystem\ExamSubjectMaster;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExamPacketController extends Controller
{
  /**
   * List all packets with filters.
   */
  public function index(Request $request)
  {
    $query = ExamPacket::with(['examSession', 'subjectMaster', 'evaluator', 'generatedByUser']);

    if ($request->filled('exam_session_id')) {
      $query->where('exam_session_id', $request->exam_session_id);
    }

    if ($request->filled('erp_subject_id')) {
      $query->where('erp_subject_id', $request->erp_subject_id);
    }

    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    if ($request->filled('search')) {
      $query->where('packet_number', 'like', "%{$request->search}%");
    }

    $packets = $query->orderBy('created_at', 'desc')->paginate(30);
    $examSessions = ExamSession::orderBy('start_date', 'desc')->get();
    $subjects = ExamSubjectMaster::orderBy('subject_code')->get();

    // Stats
    $totalPackets = ExamPacket::count();
    $generatedCount = ExamPacket::where('status', 'generated')->count();
    $assignedCount = ExamPacket::where('status', 'assigned')->count();
    $completedCount = ExamPacket::where('status', 'completed')->count();

    return view('coe.packets.index', compact(
      'packets',
      'examSessions',
      'subjects',
      'totalPackets',
      'generatedCount',
      'assignedCount',
      'completedCount'
    ));
  }

  /**
   * Show the packet generation form.
   */
  public function generate(Request $request)
  {
    $examSessions = ExamSession::orderBy('start_date', 'desc')->get();
    $subjects = ExamSubjectMaster::orderBy('subject_code')->get();

    $presentStudents = collect();
    $existingPackets = collect();
    $selectedSession = null;
    $selectedSubject = null;
    $alreadyPacketed = collect();

    if ($request->filled('exam_session_id') && $request->filled('erp_subject_id')) {
      $selectedSession = ExamSession::find($request->exam_session_id);
      $selectedSubject = ExamSubjectMaster::where('erp_subject_id', $request->erp_subject_id)->first();

      if ($selectedSession && $selectedSubject) {
        // Get present students from attendance
        $presentStudentIds = ExamAttendance::where('exam_id', $selectedSession->id)
          ->where('subject_id', $request->erp_subject_id)
          ->where('status', 'present')
          ->pluck('student_id');

        $presentStudents = \App\Models\StudentMaster::whereIn('id', $presentStudentIds)
          ->orderBy('roll_no')
          ->get();

        // Get students already in packets for this session+subject
        $alreadyPacketed = ExamPacketStudent::whereHas('packet', function ($q) use ($request) {
          $q->where('exam_session_id', $request->exam_session_id)
            ->where('erp_subject_id', $request->erp_subject_id);
        })->pluck('erp_student_id');

        // Get existing packets for this session+subject
        $existingPackets = ExamPacket::with(['evaluator', 'students'])
          ->where('exam_session_id', $request->exam_session_id)
          ->where('erp_subject_id', $request->erp_subject_id)
          ->orderBy('packet_number')
          ->get();
      }
    }

    return view('coe.packets.generate', compact(
      'examSessions',
      'subjects',
      'presentStudents',
      'existingPackets',
      'selectedSession',
      'selectedSubject',
      'alreadyPacketed'
    ));
  }

  /**
   * Auto-generate packets from present students.
   */
  public function store(Request $request)
  {
    $request->validate([
      'exam_session_id' => 'required|integer|exists:exam_sessions,id',
      'erp_subject_id' => 'required|integer',
      'packet_size' => 'required|integer|min:20|max:30',
    ]);

    $examSessionId = $request->exam_session_id;
    $erpSubjectId = $request->erp_subject_id;
    $packetSize = $request->packet_size;

    // Get present students from attendance
    $presentStudentIds = ExamAttendance::where('exam_id', $examSessionId)
      ->where('subject_id', $erpSubjectId)
      ->where('status', 'present')
      ->pluck('student_id')
      ->toArray();

    if (empty($presentStudentIds)) {
      return redirect()->back()
        ->with('error', 'No present students found for this session/subject. Please mark attendance first.')
        ->withInput();
    }

    // Exclude students already in packets for this session+subject
    $alreadyPacketed = ExamPacketStudent::whereHas('packet', function ($q) use ($examSessionId, $erpSubjectId) {
      $q->where('exam_session_id', $examSessionId)
        ->where('erp_subject_id', $erpSubjectId);
    })->pluck('erp_student_id')->toArray();

    $remainingStudents = array_values(array_diff($presentStudentIds, $alreadyPacketed));

    if (empty($remainingStudents)) {
      return redirect()->back()
        ->with('error', 'All present students are already assigned to packets.')
        ->withInput();
    }

    // Get session and subject for packet number prefix
    $session = ExamSession::find($examSessionId);
    $subject = ExamSubjectMaster::where('erp_subject_id', $erpSubjectId)->first();
    $subjectCode = $subject ? $subject->subject_code : 'SUB';

    // Get dummy numbers for mapping
    $dummyNumbers = ExamDummyNumber::where('exam_session_id', $examSessionId)
      ->whereIn('erp_student_id', $remainingStudents)
      ->pluck('dummy_number', 'erp_student_id')
      ->toArray();

    // Determine next packet sequence number
    $lastPacket = ExamPacket::where('exam_session_id', $examSessionId)
      ->where('erp_subject_id', $erpSubjectId)
      ->orderBy('id', 'desc')
      ->first();

    $nextSeq = 1;
    if ($lastPacket) {
      // Extract sequence from packet_number like "PKT-CS101-S1-003"
      $parts = explode('-', $lastPacket->packet_number);
      $lastSeq = intval(end($parts));
      $nextSeq = $lastSeq + 1;
    }

    // Chunk students into packets
    $chunks = array_chunk($remainingStudents, $packetSize);
    $created = 0;

    try {
      DB::beginTransaction();

      foreach ($chunks as $chunk) {
        $packetNumber = sprintf('PKT-%s-S%d-%03d', $subjectCode, $examSessionId, $nextSeq);

        $packet = ExamPacket::create([
          'exam_session_id' => $examSessionId,
          'erp_subject_id' => $erpSubjectId,
          'packet_number' => $packetNumber,
          'total_scripts' => count($chunk),
          'status' => 'generated',
          'generated_by' => Auth::id(),
        ]);

        foreach ($chunk as $studentId) {
          ExamPacketStudent::create([
            'exam_packet_id' => $packet->id,
            'erp_student_id' => $studentId,
            'dummy_number' => $dummyNumbers[$studentId] ?? null,
          ]);
        }

        $nextSeq++;
        $created++;
      }

      DB::commit();

      Log::info('Packets generated', [
        'session' => $examSessionId,
        'subject' => $erpSubjectId,
        'packets_created' => $created,
        'total_students' => count($remainingStudents),
        'packet_size' => $packetSize,
        'user' => Auth::id(),
      ]);

      return redirect()->route('coe.packets.generate', [
        'exam_session_id' => $examSessionId,
        'erp_subject_id' => $erpSubjectId,
      ])->with('success', "{$created} packet(s) generated with " . count($remainingStudents) . " scripts.");
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Packet generation failed', ['error' => $e->getMessage()]);

      return redirect()->back()
        ->with('error', 'Packet generation failed: ' . $e->getMessage())
        ->withInput();
    }
  }

  /**
   * View a single packet's details.
   */
  public function show($id)
  {
    $packet = ExamPacket::with([
      'examSession',
      'subjectMaster',
      'evaluator',
      'generatedByUser',
      'students.student',
    ])->findOrFail($id);

    return view('coe.packets.show', compact('packet'));
  }

  /**
   * Assign an evaluator to a packet.
   */
  public function assignEvaluator(Request $request)
  {
    $request->validate([
      'packet_id' => 'required|integer|exists:exam_packets,id',
      'evaluator_id' => 'required|integer|exists:users,id',
    ]);

    try {
      $packet = ExamPacket::findOrFail($request->packet_id);

      $packet->update([
        'evaluator_id' => $request->evaluator_id,
        'status' => 'assigned',
        'assigned_at' => now(),
      ]);

      Log::info('Evaluator assigned to packet', [
        'packet_id' => $packet->id,
        'packet_number' => $packet->packet_number,
        'evaluator_id' => $request->evaluator_id,
        'assigned_by' => Auth::id(),
      ]);

      return redirect()->back()->with('success', "Evaluator assigned to packet {$packet->packet_number} successfully.");
    } catch (\Exception $e) {
      Log::error('Evaluator assignment failed', ['error' => $e->getMessage()]);
      return redirect()->back()->with('error', 'Failed to assign evaluator: ' . $e->getMessage());
    }
  }

  /**
   * Update packet status (e.g., mark as completed).
   */
  public function updateStatus(Request $request)
  {
    $request->validate([
      'packet_id' => 'required|integer|exists:exam_packets,id',
      'status' => 'required|in:generated,assigned,evaluating,completed',
    ]);

    try {
      $packet = ExamPacket::findOrFail($request->packet_id);
      $updateData = ['status' => $request->status];

      if ($request->status === 'completed') {
        $updateData['completed_at'] = now();
      }

      $packet->update($updateData);

      Log::info('Packet status updated', [
        'packet_id' => $packet->id,
        'packet_number' => $packet->packet_number,
        'new_status' => $request->status,
        'updated_by' => Auth::id(),
      ]);

      return redirect()->back()->with('success', "Packet {$packet->packet_number} status updated to {$request->status}.");
    } catch (\Exception $e) {
      Log::error('Packet status update failed', ['error' => $e->getMessage()]);
      return redirect()->back()->with('error', 'Failed to update status: ' . $e->getMessage());
    }
  }
}
