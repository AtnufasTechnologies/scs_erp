@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Evaluation Duties</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.evaluation-duties.index') }}">Evaluation Duties</a></li>
            <li class="breadcrumb-item active" aria-current="page">Duty Details</li>
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
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-info-circle me-2"></i>Evaluation Duty Details</h3>
                  <p class="text-white-50 mb-0">View evaluation duty assignment and progress</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('admin.evaluation-duties.edit', $duty->id) }}" class="btn btn-light me-2">
                    <i class="fas fa-edit me-1"></i>Edit
                  </a>
                  <a href="{{ route('admin.evaluation-duties.index') }}" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i>Back
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-8">
          <!-- Duty Details Card -->
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold"><i class="fas fa-clipboard-list me-2 text-primary"></i>Assignment Information</h5>
            </div>
            <div class="card-body p-0">
              <table class="table table-borderless mb-0">
                <tbody>
                  <tr>
                    <th class="ps-4 py-3" style="width: 200px;">Faculty</th>
                    <td class="py-3">
                      <span class="fw-semibold">{{ $duty->faculty->FIRST_NAME ?? '' }} {{ $duty->faculty->LAST_NAME ?? '' }}</span>
                      @if($duty->faculty && $duty->faculty->DEPARTMENT)
                      <br><small class="text-muted">{{ $duty->faculty->DEPARTMENT }}</small>
                      @endif
                    </td>
                  </tr>
                  <tr class="bg-light">
                    <th class="ps-4 py-3">Subject</th>
                    <td class="py-3">
                      <span class="fw-semibold">{{ $duty->subject->subject_code ?? 'N/A' }}</span>
                      @if($duty->subject && $duty->subject->name)
                      <br><small class="text-muted">{{ $duty->subject->name }}</small>
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <th class="ps-4 py-3">Exam</th>
                    <td class="py-3">{{ $duty->exam->name ?? 'N/A' }}</td>
                  </tr>
                  <tr class="bg-light">
                    <th class="ps-4 py-3">Copies Assigned</th>
                    <td class="py-3"><span class="badge bg-secondary fs-6">{{ $duty->copies_assigned }}</span></td>
                  </tr>
                  <tr>
                    <th class="ps-4 py-3">Copies Evaluated</th>
                    <td class="py-3"><span class="badge bg-primary fs-6">{{ $duty->copies_evaluated }}</span></td>
                  </tr>
                  <tr class="bg-light">
                    <th class="ps-4 py-3">Status</th>
                    <td class="py-3">
                      @if($duty->status === 'pending')
                      <span class="badge bg-secondary fs-6">Pending</span>
                      @elseif($duty->status === 'in_progress')
                      <span class="badge bg-warning fs-6">In Progress</span>
                      @elseif($duty->status === 'completed')
                      <span class="badge bg-success fs-6">Completed</span>
                      @else
                      <span class="badge bg-light text-dark fs-6 text-capitalize">{{ $duty->status }}</span>
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <th class="ps-4 py-3">Created</th>
                    <td class="py-3">{{ $duty->created_at ? $duty->created_at->format('d M Y, h:i A') : 'N/A' }}</td>
                  </tr>
                  <tr class="bg-light">
                    <th class="ps-4 py-3">Last Updated</th>
                    <td class="py-3">{{ $duty->updated_at ? $duty->updated_at->format('d M Y, h:i A') : 'N/A' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Progress Card -->
          <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold"><i class="fas fa-chart-bar me-2 text-primary"></i>Evaluation Progress</h5>
            </div>
            <div class="card-body">
              <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">{{ $duty->copies_evaluated }} of {{ $duty->copies_assigned }} copies evaluated</span>
                <span class="fw-bold">{{ $duty->progress }}%</span>
              </div>
              <div class="progress" style="height: 30px;">
                <input type="hidden" id="jsShowProgress" value="{{ $duty->progress }}">
                <div class="progress-bar progress-bar-striped progress-bar-animated {{ $duty->progress >= 100 ? 'bg-success' : ($duty->progress >= 50 ? 'bg-info' : 'bg-warning') }}"
                  role="progressbar" style="width: 0%"
                  aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                  0%
                </div>
              </div>
              <div class="mt-2 text-muted small">
                <i class="fas fa-info-circle me-1"></i>
                {{ $duty->copies_assigned - $duty->copies_evaluated }} copies remaining
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold"><i class="fas fa-cogs me-2 text-primary"></i>Actions</h5>
            </div>
            <div class="card-body">
              @if($duty->status !== 'completed')
              <form action="{{ route('admin.evaluation-duties.mark-completed', $duty->id) }}" method="POST" class="mb-3">
                @csrf
                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Mark as completed? This will set copies evaluated = copies assigned.')">
                  <i class="fas fa-check-circle me-2"></i>Mark as Completed
                </button>
              </form>
              @endif
              <a href="{{ route('admin.evaluation-duties.edit', $duty->id) }}" class="btn btn-primary w-100 mb-3">
                <i class="fas fa-edit me-2"></i>Edit Duty
              </a>
              <form action="{{ route('admin.evaluation-duties.destroy', $duty->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Delete this evaluation duty?')">
                  <i class="fas fa-trash me-2"></i>Delete Duty
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var progressEl = document.getElementById('jsShowProgress');
    if (progressEl) {
      var val = parseInt(progressEl.value) || 0;
      var bar = progressEl.parentElement.querySelector('.progress-bar');
      if (bar) {
        setTimeout(function() {
          bar.style.width = val + '%';
          bar.setAttribute('aria-valuenow', val);
          bar.textContent = val + '%';
        }, 300);
      }
    }
  });
</script>