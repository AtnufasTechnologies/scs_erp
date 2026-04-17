@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <main class="page-content">
    <!-- Breadcrumb -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Exam Attendance</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.exam-attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item active" aria-current="page">Details</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid">
      <!-- Header Card -->
      <div class="card shadow-sm mb-4">
        <div class="card-header gradient-coe text-white py-3">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h5 class="mb-0 fw-bold">
                <i class="fas fa-clipboard-check me-2"></i>Attendance Details
              </h5>
            </div>
            <div class="col-md-4 text-md-end">
              <a href="{{ route('admin.exam-attendance.index') }}" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to List
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Attendance Details -->
      <div class="row">
        <div class="col-lg-8 mx-auto">
          <div class="card shadow-sm">
            <div class="card-body p-4">
              <!-- Student Information -->
              <div class="row mb-4">
                <div class="col-md-6">
                  <h6 class="text-muted mb-2"><i class="fas fa-user me-2"></i>Student Information</h6>
                  <div class="ps-4">
                    <p class="mb-2"><strong>Name:</strong> {{ $attendance->student->full_name ?? 'N/A' }}</p>
                    <p class="mb-2"><strong>Registration No:</strong> {{ $attendance->student->registration_no ?? 'N/A' }}</p>
                    <p class="mb-2"><strong>Programme:</strong> {{ $attendance->student->programme->name ?? 'N/A' }}</p>
                  </div>
                </div>

                <div class="col-md-6">
                  <h6 class="text-muted mb-2"><i class="fas fa-clipboard-list me-2"></i>Exam Information</h6>
                  <div class="ps-4">
                    <p class="mb-2"><strong>Exam:</strong> {{ $attendance->exam->name ?? 'N/A' }}</p>
                    <p class="mb-2"><strong>Date:</strong> {{ \Carbon\Carbon::parse($attendance->date)->format('d M Y') }}</p>
                    <p class="mb-2"><strong>Type:</strong> {{ $attendance->exam->exam_type ?? 'N/A' }}</p>
                  </div>
                </div>
              </div>

              <hr>

              <!-- Attendance Status -->
              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="text-muted mb-3"><i class="fas fa-check-circle me-2"></i>Attendance Status</h6>
                  <div class="ps-4">
                    <div class="mb-3">
                      <strong>Status:</strong>
                      @if($attendance->status === 'present')
                      <span class="badge bg-success ms-2"><i class="fas fa-check me-1"></i>Present</span>
                      @elseif($attendance->status === 'absent')
                      <span class="badge bg-danger ms-2"><i class="fas fa-times me-1"></i>Absent</span>
                      @elseif($attendance->status === 'late')
                      <span class="badge bg-warning text-dark ms-2"><i class="fas fa-clock me-1"></i>Late</span>
                      @else
                      <span class="badge bg-secondary ms-2">{{ ucfirst($attendance->status) }}</span>
                      @endif
                    </div>

                    @if($attendance->remarks)
                    <div class="mb-3">
                      <strong>Remarks:</strong>
                      <p class="mb-0 mt-2 p-3 bg-light rounded">{{ $attendance->remarks }}</p>
                    </div>
                    @endif

                    @if($attendance->marked_by)
                    <div class="mb-3">
                      <strong>Marked By:</strong> {{ $attendance->markedBy->name ?? 'System' }}
                    </div>
                    @endif

                    @if($attendance->marked_at)
                    <div class="mb-3">
                      <strong>Marked At:</strong> {{ \Carbon\Carbon::parse($attendance->marked_at)->format('d M Y, h:i A') }}
                    </div>
                    @endif
                  </div>
                </div>
              </div>

              <hr>

              <!-- Record Details -->
              <div class="row">
                <div class="col-12">
                  <h6 class="text-muted mb-3"><i class="fas fa-info-circle me-2"></i>Record Information</h6>
                  <div class="ps-4">
                    <p class="mb-2"><strong>Created:</strong> {{ $attendance->created_at->format('d M Y, h:i A') }}</p>
                    @if($attendance->updated_at->ne($attendance->created_at))
                    <p class="mb-2"><strong>Last Updated:</strong> {{ $attendance->updated_at->format('d M Y, h:i A') }}</p>
                    @endif
                  </div>
                </div>
              </div>

              <!-- Actions -->
              <div class="d-flex gap-2 justify-content-end mt-4">
                <a href="{{ route('admin.exam-attendance.edit', $attendance->id) }}" class="btn btn-warning">
                  <i class="fas fa-edit me-1"></i>Edit
                </a>
                <form action="{{ route('admin.exam-attendance.destroy', $attendance->id) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this attendance record?')">
                    <i class="fas fa-trash me-1"></i>Delete
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<style>
  .gradient-coe {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }
</style>

@include('includes.footer')