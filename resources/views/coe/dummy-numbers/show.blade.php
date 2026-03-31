@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Dummy Numbers</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.dummy-numbers.index') }}">Dummy Numbers</a></li>
            <li class="breadcrumb-item active" aria-current="page">Details</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <div class="card shadow-sm mb-4">
        <div class="card-header gradient-coe text-white py-3">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h5 class="mb-0 fw-bold"><i class="fas fa-sort-numeric-down me-2"></i>Dummy Number Details</h5>
            </div>
            <div class="col-md-4 text-md-end">
              <a href="{{ route('coe.dummy-numbers.index') }}" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to List
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-8 mx-auto">
          <div class="card shadow-sm">
            <div class="card-body p-4">
              <div class="row mb-4">
                <div class="col-md-6">
                  <h6 class="text-muted mb-2"><i class="fas fa-hashtag me-2"></i>Dummy Number</h6>
                  <div class="ps-4">
                    <p class="mb-2 fs-4 fw-bold text-primary">{{ $dummyNumber->dummy_number }}</p>
                    <p class="mb-2">
                      <strong>Status:</strong>
                      @if($dummyNumber->locked)
                      <span class="badge bg-danger"><i class="fas fa-lock me-1"></i>Locked</span>
                      @else
                      <span class="badge bg-success"><i class="fas fa-unlock me-1"></i>Unlocked</span>
                      @endif
                    </p>
                  </div>
                </div>
                <div class="col-md-6">
                  <h6 class="text-muted mb-2"><i class="fas fa-clipboard-list me-2"></i>Exam Information</h6>
                  <div class="ps-4">
                    <p class="mb-2"><strong>Exam:</strong> {{ $dummyNumber->exam->name ?? 'N/A' }}</p>
                    <p class="mb-2"><strong>Date:</strong> {{ $dummyNumber->exam ? \Carbon\Carbon::parse($dummyNumber->exam->exam_date)->format('d M Y') : 'N/A' }}</p>
                    <p class="mb-2"><strong>Type:</strong> {{ $dummyNumber->exam->exam_type ?? 'N/A' }}</p>
                  </div>
                </div>
              </div>

              <hr>

              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="text-muted mb-2"><i class="fas fa-user me-2"></i>Student Information</h6>
                  <div class="ps-4">
                    <p class="mb-2"><strong>Name:</strong> {{ $dummyNumber->examStudent->student->first_name ?? '' }} {{ $dummyNumber->examStudent->student->last_name ?? '' }}</p>
                    <p class="mb-2"><strong>Enrollment No:</strong> {{ $dummyNumber->examStudent->enrollment_no ?? 'N/A' }}</p>
                    <p class="mb-2"><strong>Roll No:</strong> {{ $dummyNumber->examStudent->student->roll_no ?? 'N/A' }}</p>
                  </div>
                </div>
              </div>

              <hr>

              <div class="row">
                <div class="col-12">
                  <h6 class="text-muted mb-2"><i class="fas fa-info-circle me-2"></i>Record Information</h6>
                  <div class="ps-4">
                    <p class="mb-2"><strong>Created:</strong> {{ $dummyNumber->created_at ? $dummyNumber->created_at->format('d M Y, h:i A') : 'N/A' }}</p>
                    @if($dummyNumber->updated_at && $dummyNumber->created_at && $dummyNumber->updated_at->ne($dummyNumber->created_at))
                    <p class="mb-2"><strong>Last Updated:</strong> {{ $dummyNumber->updated_at->format('d M Y, h:i A') }}</p>
                    @endif
                  </div>
                </div>
              </div>

              @if(!$dummyNumber->locked)
              <div class="d-flex gap-2 justify-content-end mt-4">
                <a href="{{ route('coe.dummy-numbers.edit', $dummyNumber->id) }}" class="btn btn-warning">
                  <i class="fas fa-edit me-1"></i>Edit
                </a>
                <form action="{{ route('coe.dummy-numbers.destroy', $dummyNumber->id) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this dummy number?')">
                    <i class="fas fa-trash me-1"></i>Delete
                  </button>
                </form>
              </div>
              @endif
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