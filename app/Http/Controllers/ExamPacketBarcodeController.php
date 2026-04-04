<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\ExamPacket;
use App\Models\ExamSystem\ExamPacketScanLog;
use App\Models\ExamSystem\ExamSession;
use App\Models\ExamSystem\ExamSubjectMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExamPacketBarcodeController extends Controller
{
  /**
   * Generate barcodes for packets that don't have one yet.
   */
  public function generateBarcodes(Request $request)
  {
    $request->validate([
      'packet_ids' => 'required|array|min:1',
      'packet_ids.*' => 'integer|exists:exam_packets,id',
    ]);

    $count = 0;

    try {
      DB::beginTransaction();

      $packets = ExamPacket::whereIn('id', $request->packet_ids)
        ->whereNull('barcode')
        ->get();

      foreach ($packets as $packet) {
        $barcode = ExamPacket::generateBarcode($packet->packet_number);

        // Ensure uniqueness
        while (ExamPacket::where('barcode', $barcode)->exists()) {
          $barcode = ExamPacket::generateBarcode($packet->packet_number);
        }

        $packet->update(['barcode' => $barcode]);
        $count++;
      }

      DB::commit();

      Log::info('Barcodes generated', [
        'count' => $count,
        'user' => Auth::id(),
      ]);

      return redirect()->back()->with('success', "{$count} barcode(s) generated successfully.");
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Barcode generation failed', ['error' => $e->getMessage()]);
      return redirect()->back()->with('error', 'Barcode generation failed: ' . $e->getMessage());
    }
  }

  /**
   * Print barcode labels page for selected packets.
   */
  public function printLabels(Request $request)
  {
    $packetIds = $request->input('packet_ids', []);

    if (empty($packetIds)) {
      // If no specific packets, show all with barcodes
      $packets = ExamPacket::with(['examSession', 'subjectMaster'])
        ->whereNotNull('barcode')
        ->orderBy('packet_number')
        ->get();
    } else {
      $packets = ExamPacket::with(['examSession', 'subjectMaster'])
        ->whereIn('id', $packetIds)
        ->whereNotNull('barcode')
        ->orderBy('packet_number')
        ->get();
    }

    return view('coe.packets.print-labels', compact('packets'));
  }

  /**
   * Scanner page - mobile-friendly barcode scanner.
   */
  public function scanner()
  {
    return view('coe.packets.scanner');
  }

  /**
   * Process a barcode scan via AJAX.
   */
  public function processScan(Request $request)
  {
    $request->validate([
      'barcode' => 'required|string|max:100',
      'action' => 'required|in:received,transferred,returned,status_update',
      'holder_name' => 'nullable|string|max:255',
      'holder_role' => 'nullable|string|max:100',
      'new_status' => 'nullable|in:generated,assigned,evaluating,completed',
      'remarks' => 'nullable|string|max:500',
      'latitude' => 'nullable|numeric',
      'longitude' => 'nullable|numeric',
    ]);

    $packet = ExamPacket::where('barcode', $request->barcode)->first();

    if (!$packet) {
      return response()->json([
        'success' => false,
        'message' => 'Invalid barcode. No packet found.',
      ], 404);
    }

    try {
      DB::beginTransaction();

      $previousStatus = $packet->status;
      $updateData = [
        'last_scanned_at' => now(),
      ];

      // Update holder info
      if ($request->filled('holder_name')) {
        $updateData['current_holder_name'] = $request->holder_name;
      }
      if ($request->filled('holder_role')) {
        $updateData['current_holder_role'] = $request->holder_role;
      }

      // Update status if requested
      if ($request->action === 'status_update' && $request->filled('new_status')) {
        $updateData['status'] = $request->new_status;
        if ($request->new_status === 'completed') {
          $updateData['completed_at'] = now();
        }
      }

      $packet->update($updateData);

      // Create scan log
      ExamPacketScanLog::create([
        'exam_packet_id' => $packet->id,
        'barcode' => $packet->barcode,
        'action' => $request->action,
        'scanned_by_name' => Auth::user()->name ?? 'Unknown',
        'scanned_by_user_id' => Auth::id(),
        'holder_name' => $request->holder_name,
        'holder_role' => $request->holder_role,
        'previous_status' => $previousStatus,
        'new_status' => $request->action === 'status_update' ? $request->new_status : $previousStatus,
        'remarks' => $request->remarks,
        'device_info' => $request->header('User-Agent'),
        'ip_address' => $request->ip(),
        'latitude' => $request->latitude,
        'longitude' => $request->longitude,
      ]);

      DB::commit();

      // Reload packet with relations
      $packet->load(['examSession', 'subjectMaster', 'evaluator']);

      Log::info('Packet scanned', [
        'packet_id' => $packet->id,
        'barcode' => $packet->barcode,
        'action' => $request->action,
        'user' => Auth::id(),
      ]);

      return response()->json([
        'success' => true,
        'message' => 'Scan recorded successfully.',
        'packet' => [
          'id' => $packet->id,
          'packet_number' => $packet->packet_number,
          'barcode' => $packet->barcode,
          'subject' => $packet->subjectMaster ? $packet->subjectMaster->subject_code . ' - ' . $packet->subjectMaster->name : 'N/A',
          'session' => $packet->examSession->name ?? 'Session #' . $packet->exam_session_id,
          'total_scripts' => $packet->total_scripts,
          'status' => $packet->status,
          'evaluator' => $packet->evaluator->name ?? 'Not Assigned',
          'current_holder' => $packet->current_holder_name ?? 'N/A',
          'current_holder_role' => $packet->current_holder_role ?? 'N/A',
          'last_scanned_at' => $packet->last_scanned_at ? $packet->last_scanned_at->format('d M Y, h:i A') : '-',
        ],
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Scan processing failed', ['error' => $e->getMessage()]);
      return response()->json([
        'success' => false,
        'message' => 'Scan processing failed. Please try again.',
      ], 500);
    }
  }

  /**
   * Lookup a barcode without performing any action (GET).
   */
  public function lookup(Request $request)
  {
    $request->validate([
      'barcode' => 'required|string|max:100',
    ]);

    $packet = ExamPacket::with(['examSession', 'subjectMaster', 'evaluator', 'scanLogs' => function ($q) {
      $q->orderBy('created_at', 'desc')->limit(10);
    }])->where('barcode', $request->barcode)->first();

    if (!$packet) {
      return response()->json([
        'success' => false,
        'message' => 'No packet found for this barcode.',
      ], 404);
    }

    return response()->json([
      'success' => true,
      'packet' => [
        'id' => $packet->id,
        'packet_number' => $packet->packet_number,
        'barcode' => $packet->barcode,
        'subject' => $packet->subjectMaster ? $packet->subjectMaster->subject_code . ' - ' . $packet->subjectMaster->name : 'N/A',
        'session' => $packet->examSession->name ?? 'Session #' . $packet->exam_session_id,
        'total_scripts' => $packet->total_scripts,
        'status' => $packet->status,
        'evaluator' => $packet->evaluator->name ?? 'Not Assigned',
        'current_holder' => $packet->current_holder_name ?? 'N/A',
        'current_holder_role' => $packet->current_holder_role ?? 'N/A',
        'last_scanned_at' => $packet->last_scanned_at ? $packet->last_scanned_at->format('d M Y, h:i A') : '-',
      ],
      'recent_scans' => $packet->scanLogs->map(function ($log) {
        return [
          'action' => $log->action,
          'action_badge' => $log->action_badge,
          'scanned_by' => $log->scanned_by_name,
          'holder' => $log->holder_name ?? '-',
          'remarks' => $log->remarks ?? '-',
          'date' => $log->created_at->format('d M Y, h:i A'),
        ];
      }),
    ]);
  }

  /**
   * Tracking dashboard - overview of all packet movements.
   */
  public function tracking(Request $request)
  {
    $examSessions = ExamSession::orderBy('start_date', 'desc')->get();
    $subjects = ExamSubjectMaster::orderBy('subject_code')->get();

    $query = ExamPacket::with(['examSession', 'subjectMaster', 'evaluator'])
      ->whereNotNull('barcode');

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
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('packet_number', 'like', "%{$search}%")
          ->orWhere('barcode', 'like', "%{$search}%")
          ->orWhere('current_holder_name', 'like', "%{$search}%");
      });
    }

    $packets = $query->orderBy('last_scanned_at', 'desc')->paginate(30);

    // Stats
    $totalBarcoded = ExamPacket::whereNotNull('barcode')->count();
    $totalScans = ExamPacketScanLog::count();
    $recentScans = ExamPacketScanLog::with(['packet'])->orderBy('created_at', 'desc')->limit(20)->get();
    $activeHolders = ExamPacket::whereNotNull('barcode')
      ->whereNotNull('current_holder_name')
      ->select('current_holder_name', 'current_holder_role', DB::raw('count(*) as packet_count'))
      ->groupBy('current_holder_name', 'current_holder_role')
      ->orderBy('packet_count', 'desc')
      ->limit(10)
      ->get();

    return view('coe.packets.tracking', compact(
      'examSessions',
      'subjects',
      'packets',
      'totalBarcoded',
      'totalScans',
      'recentScans',
      'activeHolders'
    ));
  }

  /**
   * View scan audit log for a specific packet.
   */
  public function scanHistory($packetId)
  {
    $packet = ExamPacket::with(['examSession', 'subjectMaster', 'evaluator'])->findOrFail($packetId);

    $scanLogs = ExamPacketScanLog::where('exam_packet_id', $packetId)
      ->orderBy('created_at', 'desc')
      ->paginate(50);

    return view('coe.packets.scan-history', compact('packet', 'scanLogs'));
  }
}
