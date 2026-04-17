<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\ExamMacWhitelist;
use App\Models\ExamSystem\ExamMarksAuditLog;
use App\Models\ExamSystem\ExamMarksEntry;
use App\Models\ExamSystem\ExamMarksLock;
use App\Models\ExamSystem\ExamMarksWeightage;
use App\Models\ExamSystem\ExamSession;
use App\Models\ExamSystem\ExamSubjectMaster;
use App\Models\ExamSystem\Registration;
use App\Models\StudentMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExamMarksController extends Controller
{
  /**
   * List all marks entries with filters.
   */
  public function index(Request $request)
  {
    $query = ExamMarksEntry::with(['examSession', 'student', 'subjectMaster', 'enteredByUser']);

    if ($request->filled('exam_session_id')) {
      $query->where('exam_session_id', $request->exam_session_id);
    }

    if ($request->filled('erp_subject_id')) {
      $query->where('erp_subject_id', $request->erp_subject_id);
    }

    if ($request->filled('search')) {
      $search = $request->search;
      $query->whereHas('student', function ($q) use ($search) {
        $q->where('first_name', 'like', "%{$search}%")
          ->orWhere('last_name', 'like', "%{$search}%")
          ->orWhere('roll_no', 'like', "%{$search}%");
      });
    }

    $marks = $query->orderBy('created_at', 'desc')->paginate(50);
    $examSessions = ExamSession::orderBy('start_date', 'desc')->get();
    $subjects = ExamSubjectMaster::orderBy('subject_code')->get();

    return view('coe.marks.index', compact('marks', 'examSessions', 'subjects'));
  }

  /**
   * Show subject-wise marks entry form.
   */
  public function entry(Request $request)
  {
    $examSessions = ExamSession::orderBy('start_date', 'desc')->get();
    $subjects = ExamSubjectMaster::orderBy('subject_code')->get();

    $students = collect();
    $existingMarks = collect();
    $maxMarks = null;
    $selectedSession = null;
    $selectedSubject = null;

    $marksLock = null;
    $isLocked = false;

    if ($request->filled('exam_session_id') && $request->filled('erp_subject_id')) {
      $selectedSession = ExamSession::find($request->exam_session_id);
      $selectedSubject = ExamSubjectMaster::where('erp_subject_id', $request->erp_subject_id)->first();

      // Get registered students for this session
      $studentIds = Registration::where('exam_session_id', $request->exam_session_id)
        ->where('status', 'approved')
        ->pluck('erp_student_id');

      $students = StudentMaster::whereIn('id', $studentIds)
        ->orderBy('roll_no')
        ->get();

      // Get existing marks for this session + subject
      $existingMarks = ExamMarksEntry::where('exam_session_id', $request->exam_session_id)
        ->where('erp_subject_id', $request->erp_subject_id)
        ->get()
        ->keyBy('erp_student_id');

      // Get max marks (sum of all component weightages)
      $maxMarks = ExamMarksWeightage::where('exam_session_id', $request->exam_session_id)
        ->where('erp_subject_id', $request->erp_subject_id)
        ->sum('weightage');

      // Default max marks if no weightage configured
      if (!$maxMarks) {
        $maxMarks = 100;
      }

      // Check lock status
      $marksLock = ExamMarksLock::where('exam_session_id', $request->exam_session_id)
        ->where('erp_subject_id', $request->erp_subject_id)
        ->first();
      $isLocked = $marksLock && $marksLock->is_locked;
    }

    return view('coe.marks.entry', compact(
      'examSessions',
      'subjects',
      'students',
      'existingMarks',
      'maxMarks',
      'selectedSession',
      'selectedSubject',
      'marksLock',
      'isLocked'
    ));
  }

  /**
   * Save marks for a single student (per-row AJAX submit).
   */
  public function storeSingle(Request $request)
  {
    $request->validate([
      'exam_session_id' => 'required|integer|exists:exam_sessions,id',
      'erp_student_id' => 'required|integer|exists:student_masters,id',
      'erp_subject_id' => 'required|integer',
      'marks' => 'required|numeric|min:0',
    ]);

    // Server-side max marks validation
    $maxMarks = ExamMarksWeightage::where('exam_session_id', $request->exam_session_id)
      ->where('erp_subject_id', $request->erp_subject_id)
      ->sum('weightage');

    if (!$maxMarks) {
      $maxMarks = 100;
    }

    if ($request->marks > $maxMarks) {
      return response()->json([
        'success' => false,
        'message' => "Marks cannot exceed maximum ({$maxMarks})."
      ], 422);
    }

    // Check if marks are locked
    if (ExamMarksLock::isLocked($request->exam_session_id, $request->erp_subject_id)) {
      return response()->json([
        'success' => false,
        'message' => 'Marks are locked for this session/subject. Only COE can edit locked marks.'
      ], 403);
    }

    $macAddress = $request->input('_device_mac', $request->ip());

    try {
      DB::beginTransaction();

      // Get existing entry for audit log
      $existing = ExamMarksEntry::where('exam_session_id', $request->exam_session_id)
        ->where('erp_student_id', $request->erp_student_id)
        ->where('erp_subject_id', $request->erp_subject_id)
        ->first();

      $oldMarks = $existing ? $existing->marks : null;
      $action = $existing ? 'updated' : 'created';

      $entry = ExamMarksEntry::updateOrCreate(
        [
          'exam_session_id' => $request->exam_session_id,
          'erp_student_id' => $request->erp_student_id,
          'erp_subject_id' => $request->erp_subject_id,
        ],
        [
          'marks' => $request->marks,
          'entered_by' => Auth::id(),
          'mac_address' => $macAddress,
          'entered_at' => now(),
        ]
      );

      // Create audit log
      ExamMarksAuditLog::create([
        'exam_marks_entry_id' => $entry->id,
        'exam_session_id' => $request->exam_session_id,
        'erp_student_id' => $request->erp_student_id,
        'erp_subject_id' => $request->erp_subject_id,
        'old_marks' => $oldMarks,
        'new_marks' => $request->marks,
        'action' => $action,
        'changed_by' => Auth::id(),
        'mac_address' => $macAddress,
      ]);

      DB::commit();

      Log::info('Marks entry saved', [
        'entry_id' => $entry->id,
        'session' => $request->exam_session_id,
        'student' => $request->erp_student_id,
        'subject' => $request->erp_subject_id,
        'marks' => $request->marks,
        'old_marks' => $oldMarks,
        'action' => $action,
        'user' => Auth::id(),
        'mac' => $macAddress,
      ]);

      return response()->json([
        'success' => true,
        'message' => 'Marks saved successfully.',
        'entry' => $entry,
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Marks entry failed', ['error' => $e->getMessage()]);

      return response()->json([
        'success' => false,
        'message' => 'Failed to save marks. Please try again.',
      ], 500);
    }
  }

  /**
   * Bulk save marks for all students in one submit.
   */
  public function bulkEntry(Request $request)
  {
    $request->validate([
      'exam_session_id' => 'required|integer|exists:exam_sessions,id',
      'erp_subject_id' => 'required|integer',
      'marks_data' => 'required|array',
      'marks_data.*.erp_student_id' => 'required|integer',
      'marks_data.*.marks' => 'nullable|numeric|min:0',
    ]);

    $maxMarks = ExamMarksWeightage::where('exam_session_id', $request->exam_session_id)
      ->where('erp_subject_id', $request->erp_subject_id)
      ->sum('weightage');

    if (!$maxMarks) {
      $maxMarks = 100;
    }

    // Validate no marks exceed max
    foreach ($request->marks_data as $row) {
      if (isset($row['marks']) && $row['marks'] !== null && $row['marks'] > $maxMarks) {
        return redirect()->back()
          ->with('error', "Marks for student ID {$row['erp_student_id']} exceed maximum ({$maxMarks}).")
          ->withInput();
      }
    }

    // Check if marks are locked
    if (ExamMarksLock::isLocked($request->exam_session_id, $request->erp_subject_id)) {
      return redirect()->back()
        ->with('error', 'Marks are locked for this session/subject. Only COE can edit locked marks.')
        ->withInput();
    }

    $macAddress = $request->input('_device_mac', $request->ip());
    $saved = 0;

    try {
      DB::beginTransaction();

      foreach ($request->marks_data as $row) {
        if (!isset($row['marks']) || $row['marks'] === null || $row['marks'] === '') {
          continue;
        }

        // Get existing entry for audit log
        $existing = ExamMarksEntry::where('exam_session_id', $request->exam_session_id)
          ->where('erp_student_id', $row['erp_student_id'])
          ->where('erp_subject_id', $request->erp_subject_id)
          ->first();

        $oldMarks = $existing ? $existing->marks : null;
        $action = $existing ? 'updated' : 'created';

        $entry = ExamMarksEntry::updateOrCreate(
          [
            'exam_session_id' => $request->exam_session_id,
            'erp_student_id' => $row['erp_student_id'],
            'erp_subject_id' => $request->erp_subject_id,
          ],
          [
            'marks' => $row['marks'],
            'entered_by' => Auth::id(),
            'mac_address' => $macAddress,
            'entered_at' => now(),
          ]
        );

        // Create audit log
        ExamMarksAuditLog::create([
          'exam_marks_entry_id' => $entry->id,
          'exam_session_id' => $request->exam_session_id,
          'erp_student_id' => $row['erp_student_id'],
          'erp_subject_id' => $request->erp_subject_id,
          'old_marks' => $oldMarks,
          'new_marks' => $row['marks'],
          'action' => $action,
          'changed_by' => Auth::id(),
          'mac_address' => $macAddress,
        ]);

        $saved++;
      }

      DB::commit();

      Log::info('Bulk marks entry', [
        'session' => $request->exam_session_id,
        'subject' => $request->erp_subject_id,
        'count' => $saved,
        'user' => Auth::id(),
      ]);

      return redirect()->route('coe.marks.entry', [
        'exam_session_id' => $request->exam_session_id,
        'erp_subject_id' => $request->erp_subject_id,
      ])->with('success', "{$saved} marks entries saved successfully.");
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Bulk marks entry failed', ['error' => $e->getMessage()]);

      return redirect()->back()
        ->with('error', 'Bulk entry failed: ' . $e->getMessage())
        ->withInput();
    }
  }

  /**
   * Show a single marks entry detail.
   */
  public function show($id)
  {
    $mark = ExamMarksEntry::with(['examSession', 'student', 'subjectMaster', 'enteredByUser'])
      ->findOrFail($id);

    $auditLogs = ExamMarksAuditLog::with('changedByUser')
      ->where('exam_marks_entry_id', $mark->id)
      ->orderBy('created_at', 'desc')
      ->get();

    return view('coe.marks.show', compact('mark', 'auditLogs'));
  }

  /**
   * List all whitelisted devices.
   */
  public function whitelistIndex(Request $request)
  {
    $query = ExamMacWhitelist::query();

    if ($request->filled('search')) {
      $query->where('mac_address', 'like', "%{$request->search}%");
    }

    $whitelists = $query->orderBy('created_at', 'desc')->paginate(30);

    return view('coe.marks.whitelist', compact('whitelists'));
  }

  /**
   * Add a new device to the whitelist.
   */
  public function whitelistStore(Request $request)
  {
    $request->validate([
      'mac_address' => ['required', 'string', 'max:32', 'regex:/^([0-9A-Fa-f]{2}[:\-]){5}([0-9A-Fa-f]{2})$/'],
    ], [
      'mac_address.regex' => 'MAC address must be in format XX:XX:XX:XX:XX:XX or XX-XX-XX-XX-XX-XX.',
    ]);

    $normalized = strtoupper(str_replace('-', ':', $request->mac_address));

    $exists = ExamMacWhitelist::where('mac_address', $normalized)->exists();

    if ($exists) {
      return redirect()->back()
        ->with('error', 'This MAC address is already whitelisted.')
        ->withInput();
    }

    ExamMacWhitelist::create([
      'mac_address' => $normalized,
      'added_at' => now(),
    ]);

    Log::info('MAC whitelist entry added', [
      'mac' => $normalized,
      'added_by' => Auth::id(),
    ]);

    return redirect()->route('coe.marks.whitelist')
      ->with('success', 'Device whitelisted successfully.');
  }

  /**
   * Remove a device from the whitelist.
   */
  public function whitelistDestroy($id)
  {
    $entry = ExamMacWhitelist::findOrFail($id);

    Log::info('MAC whitelist entry removed', [
      'id' => $entry->id,
      'mac' => $entry->mac_address,
      'removed_by' => Auth::id(),
    ]);

    $entry->delete();

    return redirect()->route('coe.marks.whitelist')
      ->with('success', 'Device removed from whitelist.');
  }

  /**
   * Lock marks for a session + subject.
   */
  public function lockMarks(Request $request)
  {
    $request->validate([
      'exam_session_id' => 'required|integer|exists:exam_sessions,id',
      'erp_subject_id' => 'required|integer',
      'remarks' => 'nullable|string|max:500',
    ]);

    try {
      $lock = ExamMarksLock::updateOrCreate(
        [
          'exam_session_id' => $request->exam_session_id,
          'erp_subject_id' => $request->erp_subject_id,
        ],
        [
          'is_locked' => true,
          'locked_by' => Auth::id(),
          'locked_at' => now(),
          'remarks' => $request->remarks,
        ]
      );

      Log::info('Marks locked', [
        'session' => $request->exam_session_id,
        'subject' => $request->erp_subject_id,
        'locked_by' => Auth::id(),
      ]);

      return redirect()->back()->with('success', 'Marks have been locked successfully. Only COE can now edit these marks.');
    } catch (\Exception $e) {
      Log::error('Marks lock failed', ['error' => $e->getMessage()]);
      return redirect()->back()->with('error', 'Failed to lock marks: ' . $e->getMessage());
    }
  }

  /**
   * Unlock marks for a session + subject (COE only).
   */
  public function unlockMarks(Request $request)
  {
    $request->validate([
      'exam_session_id' => 'required|integer|exists:exam_sessions,id',
      'erp_subject_id' => 'required|integer',
      'remarks' => 'nullable|string|max:500',
    ]);

    try {
      $lock = ExamMarksLock::where('exam_session_id', $request->exam_session_id)
        ->where('erp_subject_id', $request->erp_subject_id)
        ->first();

      if (!$lock) {
        return redirect()->back()->with('error', 'No lock record found for this session/subject.');
      }

      $lock->update([
        'is_locked' => false,
        'unlocked_by' => Auth::id(),
        'unlocked_at' => now(),
        'remarks' => $request->remarks ?? $lock->remarks,
      ]);

      Log::info('Marks unlocked', [
        'session' => $request->exam_session_id,
        'subject' => $request->erp_subject_id,
        'unlocked_by' => Auth::id(),
      ]);

      return redirect()->back()->with('success', 'Marks have been unlocked. Editing is now allowed.');
    } catch (\Exception $e) {
      Log::error('Marks unlock failed', ['error' => $e->getMessage()]);
      return redirect()->back()->with('error', 'Failed to unlock marks: ' . $e->getMessage());
    }
  }

  /**
   * COE override: Update marks even when locked. Creates audit trail.
   */
  public function coeOverrideUpdate(Request $request)
  {
    $request->validate([
      'exam_session_id' => 'required|integer|exists:exam_sessions,id',
      'erp_student_id' => 'required|integer|exists:student_masters,id',
      'erp_subject_id' => 'required|integer',
      'marks' => 'required|numeric|min:0',
      'remarks' => 'required|string|max:500',
    ]);

    // Server-side max marks validation
    $maxMarks = ExamMarksWeightage::where('exam_session_id', $request->exam_session_id)
      ->where('erp_subject_id', $request->erp_subject_id)
      ->sum('weightage');

    if (!$maxMarks) {
      $maxMarks = 100;
    }

    if ($request->marks > $maxMarks) {
      return response()->json([
        'success' => false,
        'message' => "Marks cannot exceed maximum ({$maxMarks})."
      ], 422);
    }

    $macAddress = $request->input('_device_mac', $request->ip());

    try {
      DB::beginTransaction();

      // Get existing entry
      $existing = ExamMarksEntry::where('exam_session_id', $request->exam_session_id)
        ->where('erp_student_id', $request->erp_student_id)
        ->where('erp_subject_id', $request->erp_subject_id)
        ->first();

      $oldMarks = $existing ? $existing->marks : null;

      $entry = ExamMarksEntry::updateOrCreate(
        [
          'exam_session_id' => $request->exam_session_id,
          'erp_student_id' => $request->erp_student_id,
          'erp_subject_id' => $request->erp_subject_id,
        ],
        [
          'marks' => $request->marks,
          'entered_by' => Auth::id(),
          'mac_address' => $macAddress,
          'entered_at' => now(),
        ]
      );

      // Create audit log with COE override action
      ExamMarksAuditLog::create([
        'exam_marks_entry_id' => $entry->id,
        'exam_session_id' => $request->exam_session_id,
        'erp_student_id' => $request->erp_student_id,
        'erp_subject_id' => $request->erp_subject_id,
        'old_marks' => $oldMarks,
        'new_marks' => $request->marks,
        'action' => 'coe_override',
        'changed_by' => Auth::id(),
        'mac_address' => $macAddress,
        'remarks' => $request->remarks,
      ]);

      DB::commit();

      Log::info('COE override marks update', [
        'entry_id' => $entry->id,
        'session' => $request->exam_session_id,
        'student' => $request->erp_student_id,
        'subject' => $request->erp_subject_id,
        'old_marks' => $oldMarks,
        'new_marks' => $request->marks,
        'remarks' => $request->remarks,
        'user' => Auth::id(),
      ]);

      return response()->json([
        'success' => true,
        'message' => 'Marks updated successfully via COE override.',
        'entry' => $entry,
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('COE override failed', ['error' => $e->getMessage()]);

      return response()->json([
        'success' => false,
        'message' => 'Failed to update marks. Please try again.',
      ], 500);
    }
  }

  /**
   * View audit log for marks entries.
   */
  public function auditLog(Request $request)
  {
    $query = ExamMarksAuditLog::with(['student', 'subjectMaster', 'examSession', 'changedByUser']);

    if ($request->filled('exam_session_id')) {
      $query->where('exam_session_id', $request->exam_session_id);
    }

    if ($request->filled('erp_subject_id')) {
      $query->where('erp_subject_id', $request->erp_subject_id);
    }

    if ($request->filled('erp_student_id')) {
      $query->where('erp_student_id', $request->erp_student_id);
    }

    if ($request->filled('action')) {
      $query->where('action', $request->action);
    }

    if ($request->filled('search')) {
      $search = $request->search;
      $query->whereHas('student', function ($q) use ($search) {
        $q->where('first_name', 'like', "%{$search}%")
          ->orWhere('last_name', 'like', "%{$search}%")
          ->orWhere('roll_no', 'like', "%{$search}%");
      });
    }

    $logs = $query->orderBy('created_at', 'desc')->paginate(50);
    $examSessions = ExamSession::orderBy('start_date', 'desc')->get();
    $subjects = ExamSubjectMaster::orderBy('subject_code')->get();

    return view('coe.marks.audit-log', compact('logs', 'examSessions', 'subjects'));
  }

  /**
   * View all marks locks.
   */
  public function locksIndex(Request $request)
  {
    $query = ExamMarksLock::with(['examSession', 'subjectMaster', 'lockedByUser', 'unlockedByUser']);

    if ($request->filled('exam_session_id')) {
      $query->where('exam_session_id', $request->exam_session_id);
    }

    if ($request->filled('status')) {
      $query->where('is_locked', $request->status === 'locked');
    }

    $locks = $query->orderBy('updated_at', 'desc')->paginate(30);
    $examSessions = ExamSession::orderBy('start_date', 'desc')->get();

    return view('coe.marks.locks', compact('locks', 'examSessions'));
  }
}
