<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\Promotion;
use App\Models\ExamSystem\Backlog;
use App\Models\ExamSystem\Student;
use App\Models\ExamSystem\StudentCredit;
use App\Models\ExamSystem\ExamSession;
use App\Models\ExamSystem\StudentPromotionHistory;
use App\Services\ExamSystem\PromotionService;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
  public function index(Request $request)
  {
    $query = Promotion::with(['student', 'examSession']);

    if ($request->filled('from_semester')) {
      $query->where('from_semester', $request->from_semester);
    }

    if ($request->filled('promotion_status')) {
      $query->where('promotion_status', $request->promotion_status);
    }

    if ($request->filled('exam_session_id')) {
      $query->where('exam_session_id', $request->exam_session_id);
    }

    if ($request->filled('search')) {
      $search = $request->search;
      $query->whereHas('student', function ($q) use ($search) {
        $q->where('enrollment_no', 'like', "%{$search}%");
      });
    }

    $promotions = $query->orderBy('created_at', 'desc')->paginate(50);

    // Stats
    $totalPromotions = Promotion::count();
    $promotedClean = Promotion::where('promotion_status', 'promoted')->count();
    $promotedWithBacklogs = Promotion::where('promotion_status', 'promoted_with_backlogs')->count();
    $withheldCount = Promotion::where('promotion_status', 'withheld')->count();

    // Backlog stats
    $totalPendingBacklogs = Backlog::where('status', 'pending')->count();
    $totalClearedBacklogs = Backlog::where('status', 'cleared')->count();

    $sessions = ExamSession::orderBy('academic_year', 'desc')->orderBy('semester')->get();

    return view('coe.promotion.index', compact(
      'promotions',
      'totalPromotions',
      'promotedClean',
      'promotedWithBacklogs',
      'withheldCount',
      'totalPendingBacklogs',
      'totalClearedBacklogs',
      'sessions'
    ));
  }

  public function show($id)
  {
    $promotion = Promotion::with(['student', 'examSession'])->findOrFail($id);

    $promotionService = new PromotionService();
    $summary = $promotionService->getStudentSummary($promotion->exam_student_id);

    // Get promotion history for this student
    $history = StudentPromotionHistory::where('exam_student_id', $promotion->exam_student_id)
      ->with('examSession')
      ->orderBy('semester')
      ->get();

    // Get backlog details with subject info
    $backlogs = Backlog::with('subject')
      ->where('exam_student_id', $promotion->exam_student_id)
      ->orderBy('status')
      ->orderBy('semester')
      ->get();

    return view('coe.promotion.show', compact('promotion', 'summary', 'history', 'backlogs'));
  }

  public function export(Request $request)
  {
    $query = Promotion::with(['student', 'examSession']);

    if ($request->filled('exam_session_id')) {
      $query->where('exam_session_id', $request->exam_session_id);
    }

    $promotions = $query->orderBy('exam_student_id')->get();
    return response()->json($promotions);
  }
}
