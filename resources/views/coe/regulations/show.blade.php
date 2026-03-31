@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Regulation Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.regulations.index') }}">Regulations</a></li>
            <li class="breadcrumb-item active" aria-current="page">Regulation Details</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid py-4">
      <!-- Page Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <h3 class="text-white fw-bold mb-2">
                    <i class="fas fa-book me-2"></i>{{ $regulation->regulation_name }}
                  </h3>
                  <p class="text-white-50 mb-0">
                    <span class="badge bg-light text-dark me-2">{{ $regulation->regulation_type }}</span>
                    <span class="badge bg-white text-dark">{{ $regulation->start_year }} - {{ $regulation->end_year }}</span>
                  </p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('coe.regulations.edit', $regulation->id) }}" class="btn btn-light me-2">
                    <i class="fa fa-edit me-1"></i>Edit
                  </a>
                  <a href="{{ route('coe.regulations.index') }}" class="btn btn-outline-light">
                    <i class="fa fa-arrow-left me-1"></i>Back
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <div class="row">
        <!-- Regulation Details -->
        <div class="col-lg-8">
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom py-3">
              <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-primary"></i>Regulation Details</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="text-muted small">Regulation Name</label>
                  <p class="fw-bold mb-0">{{ $regulation->regulation_name }}</p>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="text-muted small">Regulation Type</label>
                  <p class="mb-0"><span class="badge bg-info">{{ $regulation->regulation_type }}</span></p>
                </div>
                <div class="col-md-12 mb-3">
                  <label class="text-muted small">Program</label>
                  <p class="fw-bold mb-0">{{ $regulation->program->name }} ({{ $regulation->program->code }})</p>
                  <small class="text-muted">{{ $regulation->program->type }}</small>
                </div>
              </div>

              <hr>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="text-muted small">Start Year</label>
                  <p class="fw-bold mb-0">
                    <i class="fa fa-calendar text-primary me-2"></i>{{ $regulation->start_year }}
                  </p>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="text-muted small">End Year</label>
                  <p class="fw-bold mb-0">
                    <i class="fa fa-calendar text-primary me-2"></i>{{ $regulation->end_year }}
                  </p>
                </div>
                <div class="col-md-12 mb-3">
                  <label class="text-muted small">Duration</label>
                  <p class="fw-bold mb-0">
                    <i class="fa fa-hourglass-half text-info me-2"></i>
                    {{ $regulation->end_year - $regulation->start_year }} years
                  </p>
                </div>
              </div>

              <hr>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="text-muted small">Created At</label>
                  <p class="mb-0">{{ $regulation->created_at->format('d M Y, h:i A') }}</p>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="text-muted small">Last Updated</label>
                  <p class="mb-0">{{ $regulation->updated_at->format('d M Y, h:i A') }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Related Exams -->
          <div class="card shadow-sm">
            <div class="card-header bg-transparent border-bottom py-3">
              <h6 class="mb-0 fw-bold"><i class="fas fa-list me-2 text-primary"></i>Associated Exams</h6>
            </div>
            <div class="card-body">
              @if($exams->count() > 0)
              <div class="table-responsive">
                <table class="table table-sm table-hover">
                  <thead class="table-light">
                    <tr>
                      <th>#</th>
                      <th>Exam Name</th>
                      <th>Type</th>
                      <th>Semester</th>
                      <th>Status</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($exams as $exam)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>
                        <a href="{{ route('coe.exams.show', $exam->id) }}">{{ $exam->name }}</a>
                      </td>
                      <td><span class="badge bg-info">{{ $exam->exam_type }}</span></td>
                      <td><span class="badge bg-secondary">{{ $exam->semester ?? 'N/A' }}</span></td>
                      <td>
                        @if($exam->status === 'upcoming')
                        <span class="badge bg-warning">Upcoming</span>
                        @elseif($exam->status === 'ongoing')
                        <span class="badge bg-success">Ongoing</span>
                        @elseif($exam->status === 'completed')
                        <span class="badge bg-info">Completed</span>
                        @else
                        <span class="badge bg-danger">{{ $exam->status }}</span>
                        @endif
                      </td>
                      <td>{{ \Carbon\Carbon::parse($exam->start_date)->format('d M Y') }}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              @else
              <p class="text-muted">No exams associated with this regulation yet.</p>
              @endif
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom py-3">
              <h6 class="mb-0 fw-bold"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
              <div class="d-grid gap-2">
                <a href="{{ route('coe.exams.create') }}?regulation_id={{ $regulation->id }}" class="btn btn-outline-primary">
                  <i class="fa fa-plus me-2"></i>Create Exam
                </a>
                <a href="{{ route('coe.regulations.edit', $regulation->id) }}" class="btn btn-outline-secondary">
                  <i class="fa fa-edit me-2"></i>Edit Regulation
                </a>
                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                  <i class="fa fa-trash me-2"></i>Delete Regulation
                </button>
              </div>
            </div>
          </div>

          <!-- Statistics Card -->
          <div class="card shadow-sm">
            <div class="card-header bg-transparent border-bottom py-3">
              <h6 class="mb-0 fw-bold"><i class="fas fa-chart-pie me-2 text-success"></i>Statistics</h6>
            </div>
            <div class="card-body">
              <div class="mb-3 pb-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="text-muted">Total Exams:</span>
                  <span class="fw-bold">{{ $exams->count() }}</span>
                </div>
              </div>
              <div class="mb-3 pb-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="text-muted">Duration:</span>
                  <span class="fw-bold">{{ $regulation->end_year - $regulation->start_year }} years</span>
                </div>
              </div>
              <div class="mb-3 pb-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="text-muted">Type:</span>
                  <span class="badge bg-info">{{ $regulation->regulation_type }}</span>
                </div>
              </div>
              <div>
                <div class="d-flex justify-content-between align-items-center">
                  <span class="text-muted">Program:</span>
                  <span class="badge bg-secondary">{{ $regulation->program->code }}</span>
                </div>
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

<script>
  function confirmDelete() {
    if (confirm('Are you sure you want to delete this regulation: "{{ $regulation->regulation_name }}"? This action cannot be undone.')) {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = `{{ route('coe.regulations.destroy', $regulation->id) }}`;

      const csrfToken = document.createElement('input');
      csrfToken.type = 'hidden';
      csrfToken.name = '_token';
      csrfToken.value = '{{ csrf_token() }}';

      const methodInput = document.createElement('input');
      methodInput.type = 'hidden';
      methodInput.name = '_method';
      methodInput.value = 'DELETE';

      form.appendChild(csrfToken);
      form.appendChild(methodInput);
      document.body.appendChild(form);
      form.submit();
    }
  }
</script>

@include('includes.footer')