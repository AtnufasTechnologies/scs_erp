<?php

namespace App\Http\Controllers;

use App\Models\AcademicBlock;
use App\Models\ExamSystem\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class CoeExamRoomController extends Controller
{
  public function index()
  {
    $query = Room::query();

    if ($this->hasRoomColumn('priority')) {
      $query->orderBy('priority');
    }

    if ($this->hasRoomColumn('building')) {
      $query->orderBy('building');
    }

    if ($this->hasRoomColumn('room_number')) {
      $query->orderBy('room_number');
    } elseif ($this->hasRoomColumn('room_no')) {
      $query->orderBy('room_no');
    }

    $rooms = $query->orderBy('id', 'desc')->get();
    $rooms->transform(function ($room) {
      $roomNo = trim((string) ($room->room_no ?? ''));
      $building = trim((string) ($room->building ?? ''));
      $roomNumber = trim((string) ($room->room_number ?? ''));
      $rows = (int) ($room->rows ?? 0);
      $columns = (int) ($room->columns ?? 0);
      $capacity = (int) ($room->capacity ?? 0);

      if (($building === '' || $roomNumber === '') && $roomNo !== '' && str_contains($roomNo, '-')) {
        [$parsedBuilding, $parsedRoomNumber] = explode('-', $roomNo, 2);
        if ($building === '') {
          $building = trim((string) $parsedBuilding);
        }
        if ($roomNumber === '') {
          $roomNumber = trim((string) $parsedRoomNumber);
        }
      }

      $room->display_building = $building !== '' ? $building : 'N/A';
      $room->display_room_number = $roomNumber !== '' ? $roomNumber : ($roomNo !== '' ? $roomNo : 'N/A');

      // Fallback for legacy rows where layout columns are unavailable.
      if ($rows <= 0 && $columns <= 0 && $capacity > 0) {
        $rows = 1;
        $columns = $capacity;
      }

      $room->display_rows = $rows;
      $room->display_columns = $columns;

      return $room;
    });
    $blocks = AcademicBlock::query()->select('id', 'title')->orderBy('title')->get();

    return view('coe.exam-rooms.index', [
      'rooms' => $rooms,
      'blocks' => $blocks,
    ]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'block_id' => 'required|exists:academic_blocks,id',
    ]);

    $block = AcademicBlock::query()
      ->select('id', 'title')
      ->findOrFail((int) $request->input('block_id'));

    $hasBuilding = $this->hasRoomColumn('building');
    $hasRoomNumber = $this->hasRoomColumn('room_number');
    $buildingInput = trim((string) $block->title);
    $roomNumberInput = strtoupper(trim((string) $request->input('room_number', '')));
    $computedRoomNo = $buildingInput !== '' ? ($buildingInput . '-' . $roomNumberInput) : $roomNumberInput;

    if (!$hasRoomNumber) {
      $request->merge(['room_no_computed' => $computedRoomNo]);
    }

    $validated = $request->validate([
      'block_id' => 'required|exists:academic_blocks,id',
      'room_number' => [
        'required',
        'string',
        'max:60',
        ...($hasRoomNumber
          ? [
            Rule::unique('rooms', 'room_number')->where(function ($query) use ($buildingInput, $hasBuilding) {
              if ($hasBuilding) {
                return $query->where('building', $buildingInput);
              }

              return $query;
            }),
          ]
          : []),
      ],
      'rows' => 'required|integer|min:1|max:200',
      'columns' => 'required|integer|min:1|max:200',
      'priority' => 'required|integer|min:1|max:9999',
      'room_no_computed' => $hasRoomNumber ? 'nullable' : ['required', 'string', 'max:255', Rule::unique('rooms', 'room_no')],
    ]);

    $validated['building'] = $buildingInput;
    $validated['room_number'] = strtoupper(trim((string) $validated['room_number']));
    $validated['rows'] = (int) $validated['rows'];
    $validated['columns'] = (int) $validated['columns'];
    $validated['priority'] = (int) $validated['priority'];
    $validated['capacity'] = $validated['rows'] * $validated['columns'];

    $validated['room_no'] = $validated['building'] . '-' . $validated['room_number'];
    $validated['name'] = $validated['room_number'];

    if (!$hasBuilding && $this->hasRoomColumn('location')) {
      $validated['location'] = $validated['building'];
    }

    Room::create($this->buildRoomPayload($validated));

    return redirect()->route('coe.exam-rooms.index')
      ->with('success', 'Exam room created successfully.');
  }

  public function update(Request $request, $id)
  {
    $room = Room::findOrFail($id);

    $request->validate([
      'block_id' => 'required|exists:academic_blocks,id',
    ]);

    $block = AcademicBlock::query()
      ->select('id', 'title')
      ->findOrFail((int) $request->input('block_id'));

    $hasBuilding = $this->hasRoomColumn('building');
    $hasRoomNumber = $this->hasRoomColumn('room_number');
    $buildingInput = trim((string) $block->title);
    $roomNumberInput = strtoupper(trim((string) $request->input('room_number', '')));
    $computedRoomNo = $buildingInput !== '' ? ($buildingInput . '-' . $roomNumberInput) : $roomNumberInput;

    if (!$hasRoomNumber) {
      $request->merge(['room_no_computed' => $computedRoomNo]);
    }

    $validated = $request->validate([
      'block_id' => 'required|exists:academic_blocks,id',
      'room_number' => [
        'required',
        'string',
        'max:60',
        ...($hasRoomNumber
          ? [
            Rule::unique('rooms', 'room_number')
              ->ignore($room->id)
              ->where(function ($query) use ($buildingInput, $hasBuilding) {
                if ($hasBuilding) {
                  return $query->where('building', $buildingInput);
                }

                return $query;
              }),
          ]
          : []),
      ],
      'rows' => 'required|integer|min:1|max:200',
      'columns' => 'required|integer|min:1|max:200',
      'priority' => 'required|integer|min:1|max:9999',
      'room_no_computed' => $hasRoomNumber ? 'nullable' : ['required', 'string', 'max:255', Rule::unique('rooms', 'room_no')->ignore($room->id)],
    ]);

    $validated['building'] = $buildingInput;
    $validated['room_number'] = strtoupper(trim((string) $validated['room_number']));
    $validated['rows'] = (int) $validated['rows'];
    $validated['columns'] = (int) $validated['columns'];
    $validated['priority'] = (int) $validated['priority'];
    $validated['capacity'] = $validated['rows'] * $validated['columns'];

    $validated['room_no'] = $validated['building'] . '-' . $validated['room_number'];
    $validated['name'] = $validated['room_number'];

    if (!$hasBuilding && $this->hasRoomColumn('location')) {
      $validated['location'] = $validated['building'];
    }

    $room->update($this->buildRoomPayload($validated));

    return redirect()->route('coe.exam-rooms.index')
      ->with('success', 'Exam room updated successfully.');
  }

  public function destroy($id)
  {
    $room = Room::findOrFail($id);
    $room->delete();

    return redirect()->route('coe.exam-rooms.index')
      ->with('success', 'Exam room deleted successfully.');
  }

  private function hasRoomColumn(string $column): bool
  {
    return Schema::hasColumn('rooms', $column);
  }

  private function buildRoomPayload(array $validated): array
  {
    $payload = [];
    $columns = [
      'room_no',
      'name',
      'building',
      'room_number',
      'rows',
      'columns',
      'capacity',
      'priority',
      'location',
    ];

    foreach ($columns as $column) {
      if ($this->hasRoomColumn($column) && array_key_exists($column, $validated)) {
        $payload[$column] = $validated[$column];
      }
    }

    return $payload;
  }
}
