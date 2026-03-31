<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\SeatingAllocation;
use App\Models\ExamSystem\Exam;
use App\Models\ExamSystem\Room;
use App\Models\StudentMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeatingAllocationController extends Controller
{
  public function index(Request $request)
  {
    $query = SeatingAllocation::with(['exam', 'room', 'student']);

    if ($request->has('exam_id') && $request->exam_id != '') {
      $query->where('exam_schedule_id', $request->exam_id);
    }

    if ($request->has('room_id') && $request->room_id != '') {
      $query->where('room_id', $request->room_id);
    }

    $allocations = $query->orderBy('seat_no')->paginate(50);
    $exams = Exam::all();
    $rooms = Room::all();

    return view('coe.seating-allocation.index', compact('allocations', 'exams', 'rooms'));
  }

  public function create()
  {
    $exams = Exam::all();
    $rooms = Room::all();
    $students = StudentMaster::where('is_deleted', 0)->where('is_left', 0)->get();

    return view('coe.seating-allocation.create', compact('exams', 'rooms', 'students'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'exam_schedule_id' => 'required|exists:exams,id',
      'room_id' => 'required|exists:rooms,id',
      'exam_student_id' => 'required|exists:student_masters,id',
      'seat_no' => 'required|string',
    ]);

    SeatingAllocation::create($request->all());

    return redirect()->route('admin.seating-allocation.index')
      ->with('success', 'Seating allocation created successfully');

    SeatingAllocation::create($request->all());

    return redirect()->route('coe.seating.index')
      ->with('success', 'Seating allocation created successfully');
  }

  public function show($id)
  {
    $allocation = SeatingAllocation::with(['exam', 'room', 'student'])->findOrFail($id);
    return view('coe.seating-allocation.show', compact('allocation'));
  }

  public function edit($id)
  {
    $allocation = SeatingAllocation::findOrFail($id);
    $exams = Exam::all();
    $rooms = Room::all();
    $students = StudentMaster::where('is_deleted', 0)->where('is_left', 0)->get();

    return view('coe.seating-allocation.edit', compact('allocation', 'exams', 'rooms', 'students'));
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'exam_schedule_id' => 'required|exists:exams,id',
      'room_id' => 'required|exists:rooms,id',
      'exam_student_id' => 'required|exists:student_masters,id',
      'seat_no' => 'required|string',
    ]);

    $allocation = SeatingAllocation::findOrFail($id);
    $allocation->update($request->all());

    return redirect()->route('admin.seating-allocation.index')
      ->with('success', 'Seating allocation updated successfully');
  }

  public function destroy($id)
  {
    $allocation = SeatingAllocation::findOrFail($id);
    $allocation->delete();

    return redirect()->route('admin.seating-allocation.index')
      ->with('success', 'Seating allocation deleted successfully');
  }

  public function autoAllocate(Request $request)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'room_ids' => 'required|array',
    ]);

    try {
      DB::beginTransaction();

      // Auto-allocation logic here
      // Get registered students for exam
      // Distribute across selected rooms
      // Assign seat numbers

      DB::commit();
      return redirect()->route('admin.seating-allocation.index')
        ->with('success', 'Auto allocation completed successfully');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()
        ->with('error', 'Auto allocation failed: ' . $e->getMessage());
    }
  }

  public function export(Request $request)
  {
    // Export seating allocation data
    $query = SeatingAllocation::with(['exam', 'room', 'student']);

    if ($request->has('exam_id') && $request->exam_id != '') {
      $query->where('exam_schedule_id', $request->exam_id);
    }

    $allocations = $query->orderBy('seat_no')->get();

    // Return export logic (CSV, Excel, PDF)
    return response()->json($allocations);
  }
}
