<?php

use App\Http\Controllers\StaticController;
use App\Models\BatchMaster;
use App\Models\Semester;
use App\Models\StudentProgram;
//fetch user's campus
$campusId =  StaticController::fetchCampusSettings();
if ($campusId != null) {
  //set campus id in session
  $programs = StudentProgram::with('campusmaster')->orderBy('name')->where('campus_id', $campusId)->get();
} else {
  $programs = StudentProgram::with('campusmaster')->orderBy('name')->get();
}
$semesters = Semester::all();
$batches = BatchMaster::orderBy('batch_name')->get();
?>
@include('includes.header')
@include('admin.accounts.sidebar')
<h4 class="mb-0">Fee Defaulters List</h4>
<div class="container-fluid py-4">
  <div class="row mb-3">
    <div class="col-lg-12">
      <div class="card shadow-sm">

        <div class="card-body">
          <form method="GET" class="row g-3 mb-4" action="{{route('defaulters-list')}}">
            <div class="col-md-2">
              <label for="filter_batch" class="form-label">Batch</label>
              <select class="form-select" name="filter_batch" id="filter_batch">
                <option value="">All</option>

                @foreach($batches as $batch)
                <option value="{{ $batch->id }}" {{ request('filter_batch') == $batch->id ? 'selected' : '' }}>{{ $batch->batch_name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label for="filter_program" class="form-label">Program</label>
              <select class="form-select dselect-example" name="filter_program" id="filter_program">
                <option value="">All</option>

                @foreach($programs as $program)
                <option value="{{ $program->id }}" {{ request('filter_program') == $program->id ? 'selected' : '' }}>{{ $program->code }} - {{ $program->name }} </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label for="filter_semester" class="form-label">Semester</label>
              <select class="form-select" name="filter_semester" id="filter_semester">
                <option value="">All</option>
                @foreach ($semesters as $semester)
                <option value="{{ $semester->id }}" {{ request('filter_semester') == $semester->id ? 'selected' : '' }}>{{ $semester->title }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
              <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
          </form>
          @if(count($defaulters) > 0)
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle" id="exportTable">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Student ID</th>
                  <th>Student Name</th>
                  <th>Batch</th>
                  <th>Programme</th>
                  <th>Semester</th>
                  <th>Fee Structure</th>
                  <th>Due Date</th>
                  <th>Late Days</th>
                  <th>Action</th>

                </tr>
              </thead>
              <tbody>
                @foreach($defaulters as $defaulter)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td><span class="text-uppercase">{{ $defaulter['student']->roll_no ?? 'N/A' }}</span></td>
                  <td><span class="text-capitalize">{{ $defaulter['student']->first_name ?? 'N/A' }} {{ $defaulter['student']->last_name ?? 'N/A' }}</span></td>
                  <td>{{ $defaulter['student']->batchmaster->batch_name ?? 'N/A' }}</td>
                  <td>{{ $defaulter['student']->programgroup->programInfo->code ?? 'N/A' }} - {{ $defaulter['student']->programgroup->programInfo->name ?? 'N/A' }}</td>
                  <td>{{ $defaulter['student']->current_year ?? 'N/A' }}</td>
                  <td>{{ $defaulter['fee_structure']->quarter_title ?? 'N/A' }}</td>
                  <td>{{ $defaulter['fee_structure']->due_date ? \Carbon\Carbon::parse($defaulter['fee_structure']->due_date)->format('d-M-Y') : 'N/A' }}</td>
                  <td>{{ $defaulter['late_days'] ?? 0 }}</td>
                  <td>
                    <a href="{{ route('student.fee.payments', ['roll_no' => $defaulter['student']->roll_no]) }}" class="btn btn-sm btn-main">
                      PAY
                    </a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @else
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No defaulters found.
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  $(document).ready(function() {
    $('#defaultersTable').DataTable({
      responsive: true,
      dom: 'Bfrtip',
      buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
    });
  });
</script>
@endpush

@include('includes.footer')