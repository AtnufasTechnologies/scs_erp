@include('includes.header')

<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Classes</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Hour-wise Classes</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="card mt-3">
      <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Classes - Hour Wise</h5>
        <form method="GET" action="{{ route('principal.classes.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
          <select name="campus_id" class="form-select form-select-sm" style="width: 180px;">
            <option value="">All Campuses</option>
            @foreach($campuses as $campus)
            <option value="{{ $campus->id }}" {{ (string)$selectedCampus === (string)$campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
            @endforeach
          </select>
          <input type="date" name="date" class="form-control form-control-sm" style="width: 180px;" value="{{ $selectedDate }}">
          <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
        </form>
      </div>
      <div class="card-body">
        <p class="text-muted mb-3">
          Showing classes for <strong>{{ \Carbon\Carbon::parse($selectedDate)->format('l, d M Y') }}</strong>
          @if($selectedCampus)
          — Campus: <strong>{{ $campuses->firstWhere('id', $selectedCampus)->name ?? '' }}</strong>
          @else
          — <strong>All Campuses</strong>
          @endif
        </p>

        @foreach($classesByHour as $hourId => $hourData)
        <div class="mb-4">
          <h6 class="fw-bold text-primary border-bottom pb-2">
            <i class="fas fa-clock me-1"></i> {{ $hourData['hour'] }}
            <span class="badge bg-secondary ms-2">{{ count($hourData['classes']) }} class(es)</span>
          </h6>

          @if(count($hourData['classes']) > 0)
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
              <thead class="bg-dark">
                <tr>
                  <th>Subject / Course</th>
                  <th>Faculty</th>
                  <th>Batch</th>
                  <th>Lecture Hall</th>
                  <th>Block</th>
                  <th>Campus</th>
                </tr>
              </thead>
              <tbody>
                @foreach($hourData['classes'] as $class)
                <tr>
                  <td>{{ $class->subjectCourse ? $class->subjectCourse->title : '-' }}</td>
                  <td class="text-capitalize">
                    @if($class->faculty)
                    {{ $class->faculty->FIRST_NAME }} {{ $class->faculty->LAST_NAME }}
                    @else
                    <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td>{{ $class->batch ? $class->batch->batch_name : '-' }}</td>
                  <td>{{ $class->lecturehallmaster ? $class->lecturehallmaster->title : '-' }}</td>
                  <td>{{ $class->lecturehallmaster && $class->lecturehallmaster->acblockmaster ? $class->lecturehallmaster->acblockmaster->title : '-' }}</td>
                  <td>
                    @if($class->lecturehallmaster && $class->lecturehallmaster->acblockmaster)
                    @php
                    $campusName = \App\Models\Campus::find($class->lecturehallmaster->acblockmaster->campus_id);
                    @endphp
                    {{ $campusName ? $campusName->name : '-' }}
                    @else
                    -
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @else
          <p class="text-muted ms-3">No classes scheduled for this hour.</p>
          @endif
        </div>
        @endforeach

        @if(collect($classesByHour)->sum(function($h) { return count($h['classes']); }) == 0)
        <div class="text-center py-5">
          <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
          <p class="text-muted">No classes scheduled for this day.</p>
        </div>
        @endif
      </div>
    </div>
  </main>
</div>

@include('includes.footer')