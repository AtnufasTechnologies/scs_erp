<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\Result;
use App\Models\ExamSystem\ResultLock;
use App\Models\ExamSystem\ResultSubject;
use App\Models\ExamSystem\Exam;
use App\Models\ExamSystem\ExamSession;
use App\Models\ExamSystem\ExamMarksEntry;
use App\Models\ExamSystem\ExamMarksWeightage;
use App\Models\ExamSystem\ExamSubjectMaster;
use App\Models\ExamSystem\ExamAttendance;
use App\Models\ExamSystem\MalpracticeCase;
use App\Models\ExamSystem\GradeMapping;
use App\Models\ExamSystem\Student;
use App\Models\StudentMaster;
use App\Models\ExamSystem\Promotion;
use App\Models\ExamSystem\Backlog;
use App\Models\ExamSystem\StudentCredit;
use App\Models\ExamSystem\StudentPromotionHistory;
use App\Services\ExamSystem\PromotionService;
use App\Services\ExamSystem\CreditEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamResultController extends Controller
{
  public function index(Request $request)
  {
    $query = Result::with(['student', 'exam', 'examSession', 'resultSubjects']);

    if ($request->filled('exam_session_id')) {
      $query->where('exam_session_id', $request->exam_session_id);
    }
    if ($request->filled('exam_id')) {
      $query->where('exam_id', $request->exam_id);
    }
    if ($request->filled('result_status')) {
      $query->where('result_status', $request->result_status);
    }
    if ($request->filled('published')) {
      $query->where('is_published', $request->published === 'yes');
    }

    $results = $query->orderBy('created_at', 'desc')->paginate(50);
    $exams = Exam::orderBy('name')->get();
    $sessions = ExamSession::orderBy('academic_year', 'desc')->orderBy('semester')->get();

    // Stats
    $totalResults = Result::count();
    $publishedCount = Result::where('is_published', true)->count();
    $pendingCount = Result::where('result_status', 'pending')->count();
    $passCount = Result::where('result_status', 'pass')->count();

    // Promotion stats
    $promotedCount = Promotion::where('promotion_status', 'promoted')->count();
    $promotedWithBacklogsCount = Promotion::where('promotion_status', 'promoted_with_backlogs')->count();
    $withheldCount = Promotion::where('promotion_status', 'withheld')->count();

    // Result locks indexed by session id
    $resultLocks = ResultLock::where('is_locked', true)->pluck('exam_session_id')->toArray();

    return view('coe.results.index', compact(
      'results',
      'exams',
      'sessions',
      'totalResults',
      'publishedCount',
      'pendingCount',
      'passCount',
      'promotedCount',
      'promotedWithBacklogsCount',
      'withheldCount',
      'resultLocks'
    ));
  }

  public function generate()
  {
    $sessions = ExamSession::orderBy('academic_year', 'desc')->orderBy('semester')->get();
    $exams = Exam::orderBy('name')->get();

    return view('coe.results.generate', compact('sessions', 'exams'));
  }

  public function doGenerate(Request $request)
  {
    $request->validate([
      'exam_session_id' => 'required|exists:exam_sessions,id',
      'exam_id' => 'nullable|exists:exams,id',
    ]);

    $sessionId = $request->exam_session_id;
    $session = ExamSession::findOrFail($sessionId);

    if (ResultLock::isLocked($sessionId)) {
      return redirect()->back()->with('error', 'Cannot regenerate results — session is locked.');
    }

    try {
      DB::beginTransaction();

      // Get all marks entries for this session grouped by student
      $marksEntries = ExamMarksEntry::where('exam_session_id', $sessionId)
        ->get()
        ->groupBy('erp_student_id');

      if ($marksEntries->isEmpty()) {
        return redirect()->back()->with('error', 'No marks entries found for this exam session.');
      }

      // Get weightages for this session
      $weightages = ExamMarksWeightage::where('exam_session_id', $sessionId)
        ->get()
        ->groupBy('erp_subject_id');

      $generatedCount = 0;

      foreach ($marksEntries as $studentId => $studentMarks) {
        // Find or get exam_student record
        $studentMaster = StudentMaster::find($studentId);
        if (!$studentMaster) continue;

        $examStudent = Student::where('erp_student_id', $studentId)->first();
        if (!$examStudent) continue;

        // Create or update result record
        $result = Result::updateOrCreate(
          [
            'exam_student_id' => $examStudent->id,
            'exam_session_id' => $sessionId,
          ],
          [
            'exam_id' => $request->exam_id ?? $examStudent->registrations()->latest()->value('exam_id'),
            'result_status' => 'pending',
            'is_published' => false,
          ]
        );

        // Skip if already published
        if ($result->is_published) continue;

        $totalWeightedPoints = 0;
        $totalCredits = 0;
        $allPassed = true;
        $hasWithheld = false;
        $totalPercentageMarks = 0;
        $subjectCount = 0;

        foreach ($studentMarks as $entry) {
          $subjectMaster = ExamSubjectMaster::where('erp_subject_id', $entry->erp_subject_id)->first();
          $subjectCode = $subjectMaster->subject_code ?? 'N/A';
          $subjectName = $subjectMaster->name ?? 'Unknown';
          $credits = $subjectMaster->credits ?? 0;
          $programId = $subjectMaster->program_id ?? $examStudent->program_id;

          // Determine FA/SA marks
          $faMarks = null;
          $saMarks = null;
          $totalMarks = null;
          $subjectResultStatus = 'Normal';

          // Check attendance/malpractice
          $attendance = ExamAttendance::where('exam_id', $request->exam_id ?? 0)
            ->where('student_id', $studentId)
            ->where('subject_id', $entry->erp_subject_id)
            ->first();

          if ($attendance && $attendance->status === 'absent') {
            $faMarks = 0;
            $saMarks = 0;
            $totalMarks = 0;
            $subjectResultStatus = 'Absent';
          } elseif ($attendance && $attendance->status === 'malpractice') {
            $subjectResultStatus = 'Withheld';
            $hasWithheld = true;
          } else {
            // Check malpractice cases
            $malpractice = MalpracticeCase::where('exam_id', $request->exam_id ?? 0)
              ->where('student_id', $studentId)
              ->where('subject_id', $entry->erp_subject_id)
              ->whereIn('status', ['pending', 'blocked'])
              ->first();

            if ($malpractice) {
              $subjectResultStatus = 'Withheld';
              $hasWithheld = true;
            } else {
              // Get component weightages for FA/SA split
              $subjectWeightages = $weightages->get($entry->erp_subject_id);

              if ($subjectWeightages && $subjectWeightages->count() > 0) {
                $internalWeight = $subjectWeightages->where('component', 'internal')->first();
                $externalWeight = $subjectWeightages->where('component', 'external')->first();

                if ($internalWeight && $externalWeight) {
                  $totalWeight = $internalWeight->weightage + $externalWeight->weightage;
                  $faMarks = round(($entry->marks * $internalWeight->weightage) / $totalWeight, 2);
                  $saMarks = round(($entry->marks * $externalWeight->weightage) / $totalWeight, 2);
                } else {
                  $saMarks = $entry->marks;
                  $faMarks = 0;
                }
              } else {
                // No weightage breakdown: treat marks as SA
                $saMarks = $entry->marks;
                $faMarks = 0;
              }
              $totalMarks = round(($faMarks ?? 0) + ($saMarks ?? 0), 2);
            }
          }

          // Calculate grade using grade mappings
          $grade = 'F';
          $gradePoint = 0;

          if ($subjectResultStatus === 'Normal' && $totalMarks !== null) {
            $gradeMapping = GradeMapping::where('program_id', $programId)
              ->where('min_marks', '<=', $totalMarks)
              ->where('max_marks', '>=', $totalMarks)
              ->first();

            if ($gradeMapping) {
              $grade = $gradeMapping->grade;
              $gradePoint = $gradeMapping->grade_point;
            }

            if ($grade === 'F') {
              $allPassed = false;
            }
          } elseif ($subjectResultStatus === 'Absent') {
            $grade = 'Ab';
            $gradePoint = 0;
            $allPassed = false;
          } elseif ($subjectResultStatus === 'Withheld') {
            $grade = 'W';
            $gradePoint = 0;
          }

          // Store subject result
          ResultSubject::updateOrCreate(
            [
              'result_id' => $result->id,
              'erp_subject_id' => $entry->erp_subject_id,
            ],
            [
              'subject_code' => $subjectCode,
              'subject_name' => $subjectName,
              'fa_marks' => $faMarks,
              'sa_marks' => $saMarks,
              'total_marks' => $totalMarks,
              'max_marks' => 100,
              'credits' => $credits,
              'grade_point' => $gradePoint,
              'grade' => $grade,
              'result_status' => $subjectResultStatus,
            ]
          );

          // SGPA calculation accumulators (skip withheld)
          if ($subjectResultStatus !== 'Withheld' && $credits > 0) {
            $totalWeightedPoints += ($gradePoint * $credits);
            $totalCredits += $credits;
          }

          if ($totalMarks !== null) {
            $totalPercentageMarks += $totalMarks;
            $subjectCount++;
          }
        }

        // Calculate SGPA
        $sgpa = $totalCredits > 0 ? round($totalWeightedPoints / $totalCredits, 2) : 0;
        $percentage = $subjectCount > 0 ? round($totalPercentageMarks / $subjectCount, 2) : 0;

        // Determine overall result status
        $resultStatus = 'pass';
        if ($hasWithheld) {
          $resultStatus = 'withheld';
        } elseif (!$allPassed) {
          $resultStatus = 'fail';
        }

        $result->update([
          'sgpa' => $sgpa,
          'percentage' => $percentage,
          'result_status' => $resultStatus,
        ]);

        $generatedCount++;
      }

      DB::commit();

      return redirect()->route('admin.exam-results.index')
        ->with('success', "Results generated successfully for {$generatedCount} students.");
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()
        ->with('error', 'Generation failed: ' . $e->getMessage());
    }
  }

  public function show($id)
  {
    $result = Result::with(['student', 'exam', 'examSession', 'resultSubjects'])
      ->findOrFail($id);

    $isLocked = ResultLock::isLocked($result->exam_session_id);

    return view('coe.results.show', compact('result', 'isLocked'));
  }

  public function publish(Request $request)
  {
    $request->validate([
      'exam_session_id' => 'required|exists:exam_sessions,id',
    ]);

    $sessionId = $request->exam_session_id;

    try {
      DB::beginTransaction();

      // Publish all unpublished results for this session
      $count = Result::where('exam_session_id', $sessionId)
        ->where('is_published', false)
        ->update([
          'is_published' => true,
          'published_at' => now(),
        ]);

      if ($count === 0) {
        DB::rollBack();
        return redirect()->route('admin.exam-results.index')
          ->with('error', 'No unpublished results found for this session.');
      }

      // Run credit engine — SGPA/CGPA calculation (+backlog/credit processing)
      $creditEngine = new CreditEngineService();
      $creditResult = $creditEngine->processSession($sessionId);

      // Run promotion logic for published results
      $promotionService = new PromotionService();
      $promotionResult = $promotionService->processSessionPromotion($sessionId);

      DB::commit();

      $message = "Published {$count} results. Credits processed: {$creditResult['processed']}.";
      $message .= " Promotion: {$promotionResult['promoted']} promoted, {$promotionResult['promoted_with_backlogs']} promoted with backlogs";
      if ($promotionResult['withheld'] > 0) {
        $message .= ", {$promotionResult['withheld']} withheld";
      }
      $allErrors = array_merge($creditResult['errors'] ?? [], $promotionResult['errors'] ?? []);
      if (!empty($allErrors)) {
        $message .= ". Errors: " . count($allErrors);
      }

      return redirect()->route('admin.exam-results.index')
        ->with('success', $message);
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->route('admin.exam-results.index')
        ->with('error', 'Publish failed: ' . $e->getMessage());
    }
  }

  public function unpublish(Request $request)
  {
    $request->validate([
      'exam_session_id' => 'required|exists:exam_sessions,id',
    ]);

    $sessionId = $request->exam_session_id;

    if (ResultLock::isLocked($sessionId)) {
      return redirect()->back()->with('error', 'Cannot unpublish — session results are locked.');
    }

    try {
      DB::beginTransaction();

      // Revert promotions for this session
      $promotions = Promotion::where('exam_session_id', $sessionId)->get();

      foreach ($promotions as $promotion) {
        // Revert student semester to from_semester
        $student = Student::find($promotion->exam_student_id);
        if ($student && $promotion->from_semester) {
          $student->update([
            'current_semester' => $promotion->from_semester,
            'promotion_status' => null,
          ]);
        }
      }

      // Delete promotion histories for this session
      StudentPromotionHistory::where('exam_session_id', $sessionId)->delete();

      // Delete promotions for this session
      Promotion::where('exam_session_id', $sessionId)->delete();

      // Revert all credit engine effects (credits, backlogs, CGPA)
      $creditEngine = new CreditEngineService();
      $creditEngine->revertSession($sessionId);

      // Unpublish results
      $count = Result::where('exam_session_id', $sessionId)
        ->where('is_published', true)
        ->update([
          'is_published' => false,
          'published_at' => null,
        ]);

      DB::commit();

      return redirect()->route('admin.exam-results.index')
        ->with('success', "Unpublished {$count} results and reverted promotions.");
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->route('admin.exam-results.index')
        ->with('error', 'Unpublish failed: ' . $e->getMessage());
    }
  }

  public function destroy($id)
  {
    $result = Result::findOrFail($id);

    if ($result->is_published) {
      return redirect()->back()->with('error', 'Cannot delete published results.');
    }

    if (ResultLock::isLocked($result->exam_session_id)) {
      return redirect()->back()->with('error', 'Cannot delete results — session is locked.');
    }

    $result->delete();

    return redirect()->route('admin.exam-results.index')
      ->with('success', 'Result deleted successfully.');
  }

  public function export(Request $request)
  {
    $query = Result::with(['student', 'exam', 'examSession', 'resultSubjects']);

    if ($request->filled('exam_session_id')) {
      $query->where('exam_session_id', $request->exam_session_id);
    }
    if ($request->filled('exam_id')) {
      $query->where('exam_id', $request->exam_id);
    }

    $results = $query->get();
    return response()->json($results);
  }

  public function lockResults(Request $request)
  {
    $request->validate([
      'exam_session_id' => 'required|exists:exam_sessions,id',
      'remarks' => 'nullable|string|max:500',
    ]);

    ResultLock::updateOrCreate(
      ['exam_session_id' => $request->exam_session_id],
      [
        'is_locked' => true,
        'locked_by' => auth()->id(),
        'locked_at' => now(),
        'unlocked_by' => null,
        'unlocked_at' => null,
        'remarks' => $request->remarks,
      ]
    );

    return redirect()->route('admin.exam-results.index')
      ->with('success', 'Results locked successfully for the selected session.');
  }

  public function unlockResults(Request $request)
  {
    $request->validate([
      'exam_session_id' => 'required|exists:exam_sessions,id',
    ]);

    $lock = ResultLock::where('exam_session_id', $request->exam_session_id)->first();

    if ($lock) {
      $lock->update([
        'is_locked' => false,
        'unlocked_by' => auth()->id(),
        'unlocked_at' => now(),
      ]);
    }

    return redirect()->route('admin.exam-results.index')
      ->with('success', 'Results unlocked successfully for the selected session.');
  }

  public function semesterWise(Request $request)
  {
    $sessions = ExamSession::orderBy('academic_year', 'desc')->orderBy('semester')->get();
    $selectedSession = null;
    $results = collect();
    $subjectSummary = collect();
    $lock = null;

    if ($request->filled('exam_session_id')) {
      $selectedSession = ExamSession::findOrFail($request->exam_session_id);
      $results = Result::with(['student', 'resultSubjects'])
        ->where('exam_session_id', $selectedSession->id)
        ->orderBy('result_status')
        ->get();

      // Aggregate subject-wise summary
      $allSubjects = ResultSubject::whereIn('result_id', $results->pluck('id'))
        ->get()
        ->groupBy('subject_code');

      foreach ($allSubjects as $code => $subjectResults) {
        $totalStudents = $subjectResults->count();
        $passCount = $subjectResults->where('result_status', 'Normal')->where('grade', '!=', 'F')->count();
        $failCount = $subjectResults->where('grade', 'F')->count();
        $absentCount = $subjectResults->where('result_status', 'Absent')->count();
        $withheldCount = $subjectResults->where('result_status', 'Withheld')->count();
        $avgMarks = $subjectResults->where('result_status', 'Normal')->avg('total_marks');

        $subjectSummary->push([
          'subject_code' => $code,
          'subject_name' => $subjectResults->first()->subject_name,
          'total_students' => $totalStudents,
          'pass_count' => $passCount,
          'fail_count' => $failCount,
          'absent_count' => $absentCount,
          'withheld_count' => $withheldCount,
          'avg_marks' => $avgMarks ? round($avgMarks, 2) : 0,
          'pass_percentage' => $totalStudents > 0 ? round(($passCount / $totalStudents) * 100, 1) : 0,
        ]);
      }

      $lock = ResultLock::where('exam_session_id', $selectedSession->id)->first();
    }

    $totalStudents = $results->count();
    $passedStudents = $results->where('result_status', 'pass')->count();
    $failedStudents = $results->where('result_status', 'fail')->count();
    $withheldStudents = $results->where('result_status', 'withheld')->count();

    return view('coe.results.semester-wise', compact(
      'sessions',
      'selectedSession',
      'results',
      'subjectSummary',
      'lock',
      'totalStudents',
      'passedStudents',
      'failedStudents',
      'withheldStudents'
    ));
  }
}
