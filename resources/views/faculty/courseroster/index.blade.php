@include('includes.header')

@php
$safeAssignmentRows = collect($assignmentRows ?? []);
@endphp

<div class="wrapper">
  @include('faculty.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Faculty</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Student Course Roster</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid mt-4">
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
          <div>
            <h5 class="mb-1 fw-bold"><i class="fas fa-clipboard-list me-2 text-primary"></i>Assigned Course List</h5>
            <p class="mb-0 text-muted">Courses are listed from your teaching assignments.</p>
          </div>
          <div class="text-end">
            <div class="h3 mb-0 text-primary fw-bold">{{ $safeAssignmentRows->count() }}</div>
            <small class="text-muted">Active Assignment{{ $safeAssignmentRows->count() === 1 ? '' : 's' }}</small>
          </div>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-4">#</th>
                  <th>Course Code</th>
                  <th>Course Title</th>
                  <th>Students</th>
                  <th>Delivery</th>
                  <th>Group</th>
                  <th>Shift</th>
                  <th>Room</th>
                  <th>View</th>
                  <th>Create</th>
                  <th>Edit</th>
                </tr>
              </thead>
              <tbody>
                @forelse($safeAssignmentRows as $index => $row)
                <tr>
                  <td class="ps-4 fw-semibold">{{ $index + 1 }}</td>
                  <td>{{ !empty($row['course_code']) ? $row['course_code'] : '-' }}</td>
                  <td>
                    <div class="fw-semibold">{{ $row['course_title'] ?? '-' }}</div>
                    @if(!empty($row['primary_faculty']))
                    <small class="text-muted">Primary Faculty: {{ $row['primary_faculty'] }}</small>
                    @endif
                  </td>
                  <td>
                    <span class="badge bg-info text-dark">{{ (int) ($row['student_count'] ?? 0) }}</span>
                  </td>
                  <td>{{ $row['delivery_type'] !== '' ? $row['delivery_type'] : '-' }}</td>
                  <td>{{ $row['allocation_group'] }}</td>
                  <td>{{ $row['shift'] !== '' ? $row['shift'] : '-' }}</td>
                  <td>{{ $row['room'] !== '' ? $row['room'] : '-' }}</td>
                  <td>
                    <a href="{{ route('faculty.course.roster.list', ['id' => $row['id'], 'code' => $row['course_code']]) }}">
                      <button class="btn btn-outline-primary"><i class="fa fa-eye"></i></button>
                    </a>
                  </td>
                  <td><a href="{{route('faculty.course.roster.create',['id' => $row['id'],'code'=>$row['course_code']])}}"><button class="btn btn-outline-success"><i class="fa fa-plus-circle"></i></button></a></td>
                  <td>
                    <a href="{{route('faculty.course.roster.create',['id' => $row['id'],'code'=>$row['course_code']])}}">
                      <button class="btn btn-warning"><i class="fa fa-edit"></i></button>
                    </a>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="11" class="text-center py-5">
                    <div class="text-muted">
                      <i class="fas fa-info-circle me-2"></i>No teaching assignments found for this faculty.
                    </div>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </main>
</div>
<!--end page main-->
<style>
  .form-select-lg {
    font-size: 1.1rem;
    padding: 0.75rem 1rem;
  }

  #subjectSelect {
    border: 2px solid #dee2e6;
    transition: all 0.3s ease;
  }

  #subjectSelect option {
    padding: 10px;
    font-size: 0.95rem;
  }

  #subjectSelect:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
  }

  .alert-light {
    background-color: #f8f9fa;
  }

  .btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
  }

  .card {
    border: none;
  }
</style>
@include('includes.footer')