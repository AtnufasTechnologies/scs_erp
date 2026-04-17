@include('includes.header')
<div class="wrapper">
  @include('coe.sidebar')

  <div class="p-4 mb-4 bg-gradient-primary text-white rounded-3 shadow">
    <div class="container-fluid py-3">
      <h1 class="display-6 fw-bold">Edit Seating Allocation</h1>
      <p class="fs-6 mb-0 text-dark">Update seating allocation details</p>
    </div>
  </div>

  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card shadow-sm">
          <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fa fa-edit"></i> Edit Seating Allocation</h5>
          </div>
          <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
            @endif

            <form action="{{ route('admin.seating-allocation.update', $allocation->id) }}" method="POST">
              @csrf
              @method('PUT')

              <div class="mb-3">
                <label for="exam_schedule_id" class="form-label">
                  Exam <span class="text-danger">*</span>
                </label>
                <select name="exam_schedule_id" id="exam_schedule_id" class="form-select" required>
                  <option value="">-- Select Exam --</option>
                  @foreach($exams as $exam)
                  <option value="{{ $exam->id }}"
                    {{ (old('exam_schedule_id', $allocation->exam_schedule_id) == $exam->id) ? 'selected' : '' }}>
                    {{ $exam->name }} ({{ $exam->exam_type }})
                  </option>
                  @endforeach
                </select>
              </div>

              <div class="mb-3">
                <label for="room_id" class="form-label">
                  Room <span class="text-danger">*</span>
                </label>
                <select name="room_id" id="room_id" class="form-select" required>
                  <option value="">-- Select Room --</option>
                  @foreach($rooms as $room)
                  <option value="{{ $room->id }}"
                    {{ (old('room_id', $allocation->room_id) == $room->id) ? 'selected' : '' }}>
                    {{ $room->name }} - Block: {{ $room->block ?? 'N/A' }} (Capacity: {{ $room->capacity ?? 'N/A' }})
                  </option>
                  @endforeach
                </select>
              </div>

              <div class="mb-3">
                <label for="seat_no" class="form-label">
                  Seat Number <span class="text-danger">*</span>
                </label>
                <input type="text" name="seat_no" id="seat_no" class="form-control"
                  placeholder="Enter seat number (e.g., A-01, B-15)"
                  value="{{ old('seat_no', $allocation->seat_no) }}" required>
                <small class="text-muted">Example: A-01, B-15, C-20</small>
              </div>

              <div class="mb-3">
                <label for="exam_student_id" class="form-label">
                  Student <span class="text-danger">*</span>
                </label>
                <select name="exam_student_id" id="exam_student_id" class="form-select" required>
                  <option value="">-- Select Student --</option>
                  @foreach($students as $student)
                  <option value="{{ $student->id }}"
                    {{ (old('exam_student_id', $allocation->exam_student_id) == $student->id) ? 'selected' : '' }}>
                    {{ $student->first_name }} {{ $student->last_name }}
                    (Roll: {{ $student->roll_no }}, Reg: {{ $student->register_no }})
                  </option>
                  @endforeach
                </select>
              </div>

              <div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle"></i>
                <strong>Warning:</strong> Changing the allocation may affect printed admit cards or seating charts.
              </div>

              <div class="d-flex justify-content-between">
                <a href="{{ route('admin.seating-allocation.index') }}" class="btn btn-secondary">
                  <i class="fa fa-arrow-left"></i> Back
                </a>
                <button type="submit" class="btn btn-warning">
                  <i class="fa fa-save"></i> Update Allocation
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@include('includes.footer')

<script>
  // Add Select2 for better dropdown experience if available
  $(document).ready(function() {
    if ($.fn.select2) {
      $('#exam_student_id').select2({
        placeholder: '-- Select Student --',
        allowClear: true
      });
    }
  });
</script>