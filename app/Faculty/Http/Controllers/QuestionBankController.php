<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\QuestionBank;
use App\Models\SyllabusSubunit;
use App\Models\CognitiveLevelMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionBankController extends Controller
{
  /**
   * Store a new question in the question bank.
   */
  public function store(Request $request)
  {
    $request->validate([
      'syllabus_subunit_id'       => 'required|exists:syllabus_subunits,id',
      'question_text'             => 'required|string',
      'marks'                     => 'required|integer|min:1|max:100',
      'difficulty'                => 'required|in:Easy,Medium,Hard',
      'cognitive_level_master_id' => 'required|exists:cognitive_level_masters,id',
    ]);

    $subunit = SyllabusSubunit::with(['syllabusManager', 'csoSubunit.taxonomies.rbtmaster'])->findOrFail($request->syllabus_subunit_id);
    $syllabusManager = $subunit->syllabusManager;

    if (!$syllabusManager) {
      return back()->with('error', 'Syllabus manager not found for this subunit.');
    }

    $allowedTaxonomyIds = collect(optional(optional($subunit->csoSubunit)->taxonomies)->all())
      ->map(fn($taxonomy) => (int) (optional($taxonomy->rbtmaster)->id ?? 0))
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    if ($allowedTaxonomyIds->isEmpty()) {
      return back()->with('error', 'No taxonomy level is mapped for this subunit. Please update taxonomy in syllabus manager first.');
    }

    $selectedTaxonomyId = (int) $request->cognitive_level_master_id;
    if (!$allowedTaxonomyIds->contains($selectedTaxonomyId)) {
      return back()->with('error', 'Selected taxonomy level is not applicable for this subunit.');
    }

    QuestionBank::create([
      'syllabus_subunit_id'       => $request->syllabus_subunit_id,
      'batch_id'                  => $syllabusManager->batch_id,
      'semester_id'               => $syllabusManager->semester_id,
      'subject_id'                => $syllabusManager->subject_id,
      'course_id'                 => $syllabusManager->co_id,
      'cognitive_level_master_id' => $selectedTaxonomyId,
      'user_id'                   => Auth::id(),
      'question_text'             => $request->question_text,
      'marks'                     => $request->marks,
      'difficulty'                => $request->difficulty,
    ]);

    return back()->with('success', 'Question added to the question bank successfully!');
  }

  /**
   * Remove a question (soft delete).
   */
  public function destroy($id)
  {
    $question = QuestionBank::findOrFail($id);

    if ($question->user_id !== Auth::id()) {
      return back()->with('error', 'You are not authorized to delete this question.');
    }

    $question->delete();

    return back()->with('success', 'Question removed from the question bank.');
  }
}
