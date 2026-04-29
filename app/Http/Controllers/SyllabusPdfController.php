<?php

namespace App\Http\Controllers;

use App\Models\SyllabusPdfUpload;
use App\Models\SubjectCourseMaster;
use App\Models\SubjectHasDeptAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SyllabusPdfController extends Controller
{
  // ──────────────────────────────────────────────────────────────────
  //  UPLOAD  – store a new reference PDF
  // ──────────────────────────────────────────────────────────────────

  public function store(Request $request)
  {
    $subjectId = $this->resolveSubjectId();

    $request->validate([
      'batch_id'         => 'required|exists:batch_masters,id',
      'semester_id'      => 'required|exists:semesters,id',
      'course_master_id' => 'required',
      'pdf_file'         => 'required|file|mimes:pdf|max:10240',
    ]);

    // Ensure the course belongs to this department
    $courseOwned = SubjectCourseMaster::where('subject_id', $subjectId)
      ->where('course_master_id', $request->course_master_id)
      ->exists();

    if (!$courseOwned) {
      return redirect()->back()->with('error', 'Selected course does not belong to your department.');
    }

    $file        = $request->file('pdf_file');
    $filePath    = StaticController::s3_file_uploader($file, 'syllabus-pdfs');
    $originalName = $file->getClientOriginalName();

    // Replace existing upload for same batch/semester/course (one PDF per slot)
    $existing = SyllabusPdfUpload::where('subject_id', $subjectId)
      ->where('batch_id', $request->batch_id)
      ->where('semester_id', $request->semester_id)
      ->where('course_master_id', $request->course_master_id)
      ->first();

    if ($existing) {
      // Remove old file from S3 and update record
      StaticController::s3_file_unlink(basename($existing->file_path), 'syllabus-pdfs');
      $existing->file_path    = $filePath;
      $existing->original_name = $originalName;
      $existing->uploaded_by  = Auth::id();
      $existing->save();
    } else {
      SyllabusPdfUpload::create([
        'subject_id'       => $subjectId,
        'batch_id'         => $request->batch_id,
        'semester_id'      => $request->semester_id,
        'course_master_id' => $request->course_master_id,
        'file_path'        => $filePath,
        'original_name'    => $originalName,
        'uploaded_by'      => Auth::id(),
      ]);
    }

    return redirect()->back()->with('success', 'Syllabus PDF uploaded successfully.');
  }

  // ──────────────────────────────────────────────────────────────────
  //  DESTROY  – delete a reference PDF
  // ──────────────────────────────────────────────────────────────────

  public function destroy(int $id)
  {
    $upload = SyllabusPdfUpload::where('subject_id', $this->resolveSubjectId())
      ->findOrFail($id);

    StaticController::s3_file_unlink(basename($upload->file_path), 'syllabus-pdfs');
    $upload->delete();

    return redirect()->back()->with('success', 'Syllabus PDF removed.');
  }

  // ──────────────────────────────────────────────────────────────────
  //  HELPER
  // ──────────────────────────────────────────────────────────────────

  private function resolveSubjectId(): int
  {
    return (int) SubjectHasDeptAdmin::where('user_id', Auth::id())->value('subject_id');
  }
}
