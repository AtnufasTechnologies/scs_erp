@include('includes.header')
@include('includes.dept-sidebar')
<!-- Main Content -->
<div class="main-content">

  <div class="container-fluid">
    <div class="row mb-4">
      <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h3 class="mb-2">Student List - {{ ucfirst($program->name) }}</h3>
            <p class="text-muted mb-0">
              <i class="fas fa-graduation-cap me-2"></i>Program Code: <strong>{{ $program->code }}</strong>
            </p>
          </div>
          <div>
            <a href="{{ url()->previous() }}" class="btn btn-secondary">
              <i class="fas fa-arrow-left me-2"></i>Back
            </a>
          </div>
        </div>
      </div>
    </div>


    <div class="row">
      <div class="col-lg-12">
        <div class="card shadow-sm">
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>All Students ({{ $students->count() }})</h5>

          </div>
          <div class="card-body">
            @if($students->count() > 0)
            <div class="table-responsive">
              <table class="table table-hover table-striped" id="exportTable">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th>Roll No</th>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Batch</th>
                    <th>Campus</th>
                    <th>Status</th>


                  </tr>
                </thead>
                <tbody>
                  @foreach($students as $student)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="text-uppercase">
                      <strong><a href="{{ route('department.student.profile', ['id' => $student->id, 'rollno' => $student->roll_no]) }}">{{ $student->roll_no ?? 'N/A' }}</a></strong>
                    </td>
                    <td class="text-capitalize">
                      {{ $student->first_name }} {{ $student->last_name }}
                    </td>
                    <td>
                      @if($student->mail_id)
                      <a href="mailto:{{ $student->mail_id }}">{{ $student->mail_id }}</a>
                      @else
                      <span class="text-muted">N/A</span>
                      @endif
                    </td>
                    <td>
                      @if($student->mobile_no)
                      <a href="tel:{{ $student->mobile_no }}">{{ $student->mobile_no }}</a>
                      @else
                      <span class="text-muted">N/A</span>
                      @endif
                    </td>
                    <td>
                      <span class="badge bg-info">
                        {{ $student->batchmaster->batch_name ?? 'N/A' }}
                      </span>
                    </td>
                    <td>
                      {{ $student->campusmaster->name ?? 'N/A' }}
                    </td>
                    <td>
                      @if($student->is_left == '0' )
                      <span class="badge bg-success">
                        <i class="fas fa-check-circle me-1"></i>Active
                      </span>
                      @else
                      <span class="badge bg-danger">
                        <i class="fas fa-times-circle me-1"></i>Left
                      </span>
                      @endif
                    </td>

                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @else
            <div class="alert alert-info text-center" role="alert">
              <i class="fas fa-info-circle me-2"></i>
              <strong>No students found</strong> in this program yet.
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>


  </div>
</div>

@include('includes.footer')