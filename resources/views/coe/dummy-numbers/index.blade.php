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
            <li class="breadcrumb-item active" aria-current="page">Dummy Numbers</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <!-- Page Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-sort-numeric-down me-2"></i>Dummy Numbers Management</h3>
                  <p class="text-white-50 mb-0">Assign and manage exam dummy numbers for student anonymity</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('coe.dummy-numbers.create') }}" class="btn btn-light btn-lg">
                    <i class="fas fa-plus me-2"></i>Assign New
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
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      <!-- Filters -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card shadow-sm">
            <div class="card-body">
              <form action="{{ route('coe.dummy-numbers.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Filter by Exam</label>
                  <select name="exam_id" class="form-select">
                    <option value="">All Exams</option>
                    @foreach($exams as $exam)
                    <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                      {{ $exam->name }} - {{ \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') }}
                    </option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Lock Status</label>
                  <select name="locked" class="form-select">
                    <option value="">All Status</option>
                    <option value="1" {{ request('locked') === '1' ? 'selected' : '' }}>Locked</option>
                    <option value="0" {{ request('locked') === '0' ? 'selected' : '' }}>Unlocked</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label">&nbsp;</label>
                  <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                    <a href="{{ route('coe.dummy-numbers.index') }}" class="btn btn-secondary"><i class="fas fa-redo me-1"></i>Reset</a>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Dummy Numbers Table -->
      <div class="row">
        <div class="col-12">
          <div class="card shadow-sm">
            <div class="card-header bg-light">
              <h5 class="mb-0"><i class="fas fa-list me-2"></i>Dummy Numbers List</h5>
            </div>
            <div class="card-body">
              @if($dummyNumbers->count() > 0)
              <div class="table-responsive">
                <table class="table table-hover" id="exportTable">
                  <thead class="table-light">
                    <tr>
                      <th>#</th>
                      <th>Dummy Number</th>
                      <th>Student Roll No</th>
                      <th>Student Name</th>
                      <th>Exam</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($dummyNumbers as $index => $dummy)
                    <tr>
                      <td>{{ $dummyNumbers->firstItem() + $index }}</td>
                      <td><strong class="text-primary">{{ $dummy->dummy_number }}</strong></td>
                      <td>{{ $dummy->examStudent->student->roll_no ?? ($dummy->examStudent->enrollment_no ?? 'N/A') }}</td>
                      <td>{{ $dummy->examStudent->student->first_name ?? '' }} {{ $dummy->examStudent->student->last_name ?? '' }}</td>
                      </td>
                      <td>{{ $dummy->exam->name ?? 'N/A' }}</td>
                      <td>
                        @if($dummy->locked)
                        <span class="badge bg-danger"><i class="fas fa-lock me-1"></i>Locked</span>
                        @else
                        <span class="badge bg-success"><i class="fas fa-unlock me-1"></i>Unlocked</span>
                        @endif
                      </td>
                      <td>
                        <a href="{{ route('coe.dummy-numbers.show', $dummy->id) }}" class="btn btn-sm btn-info" title="View">
                          <i class="fas fa-eye"></i>
                        </a>
                        @if(!$dummy->locked)
                        <a href="{{ route('coe.dummy-numbers.edit', $dummy->id) }}" class="btn btn-sm btn-warning" title="Edit">
                          <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('coe.dummy-numbers.destroy', $dummy->id) }}" method="POST" class="d-inline">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure?')">
                            <i class="fas fa-trash"></i>
                          </button>
                        </form>
                        @endif
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              <div class="mt-3">
                {{ $dummyNumbers->links() }}
              </div>
              @else
              <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">No dummy numbers found. <a href="{{ route('coe.dummy-numbers.create') }}">Assign one now</a></p>
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