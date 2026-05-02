<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LearningResource;
use App\Models\SyllabusSubunit;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\StaticController;

class LearningResourceController extends Controller
{
  /**
   * Display a listing of resources for a subunit.
   */
  public function index($subunitId)
  {
    $subunit = SyllabusSubunit::with([
      'csoSubunit',
      'syllabusManager.batch',
      'syllabusManager.semester',
      'syllabusManager.subject'
    ])->findOrFail($subunitId);

    $resources = LearningResource::where('syllabus_subunit_id', $subunitId)
      ->with('uploader')
      ->latest()
      ->get();

    return view('faculty.resources.index', compact('subunit', 'resources'));
  }

  /**
   * Store a newly created resource.
   */
  public function store(Request $request)
  {
    $request->validate([
      'syllabus_subunit_id' => 'required|exists:syllabus_subunits,id',
      'file' => 'required|file|max:51200', // Max 50MB
    ]);

    // Get the subunit to extract batch_id, semester_id, subject_id
    $subunit = SyllabusSubunit::with('syllabusManager')->findOrFail($request->syllabus_subunit_id);
    $syllabusManager = $subunit->syllabusManager;

    if (!$syllabusManager) {
      return back()->with('error', 'Syllabus manager not found for this subunit.');
    }

    // Get faculty ID
    $facultyId = Auth::user()->id;


    // Upload file
    $file = $request->file('file');
    $filePath = StaticController::s3_file_uploader($file, 'learning-resources');

    // Use original filename as title
    $originalFileName = $file->getClientOriginalName();

    // Create resource
    LearningResource::create([
      'syllabus_subunit_id' => $request->syllabus_subunit_id,
      'batch_id' => $syllabusManager->batch_id,
      'semester_id' => $syllabusManager->semester_id,
      'subject_id' => $syllabusManager->subject_id,
      'uploader_id' => $facultyId,
      'title' => $originalFileName,
      'description' => null,
      'file_path' => $filePath,
      'file_type' => $file->getClientOriginalExtension(),
      'file_size' => $file->getSize(),
    ]);

    return back()->with('success', 'Learning resource uploaded successfully!');
  }

  /**
   * Display the specified resource.
   */
  public function show($id)
  {
    $resource = LearningResource::with([
      'syllabusSubunit.csoSubunit',
      'batch',
      'semester',
      'subject',
      'uploader'
    ])->findOrFail($id);

    return view('faculty.resources.show', compact('resource'));
  }

  /**
   * Remove the specified resource.
   */
  public function destroy($id)
  {
    $resource = LearningResource::findOrFail($id);

    // Check if the current user is the uploader
    $userId = Auth::user()->id;
    $facultyId = Faculty::where('user_id', $userId)->value('id');

    if ($resource->uploader_id != $facultyId) {
      return back()->with('error', 'You are not authorized to delete this resource.');
    }

    // Delete file from storage
    if ($resource->file_path) {
      // If using S3 or local storage
      // Storage::delete($resource->file_path);
    }

    $resource->delete();

    return back()->with('success', 'Learning resource deleted successfully!');
  }

  /**
   * Get resources for a specific subunit (AJAX).
   */
  public function getResourcesBySubunit($subunitId)
  {
    $resources = LearningResource::where('syllabus_subunit_id', $subunitId)
      ->with('uploader')
      ->latest()
      ->get();

    return response()->json([
      'success' => true,
      'resources' => $resources
    ]);
  }
}
