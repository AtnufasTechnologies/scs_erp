@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Invigilation Duties</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.invigilation-duties.index') }}">Invigilation Duties</a></li>
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
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-info-circle me-2"></i>Duty Details</h3>
                  <p class="text-white-50 mb-0">View invigilation duty assignment details</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('admin.invigilation-duties.edit', $duty->id) }}" class="btn btn-light me-2">
                    <i class="fas fa-edit me-1"></i>Edit
                  </a>
                  <a href="{{ route('admin.invigilation-duties.index') }}" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i>Back
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Duty Details -->
      <div class="row">
        <div class="col-md-8">
          <div class="card shadow-sm border-0">
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
                    <th class="ps-4 py-3">Exam</th>
                    <td class="py-3">{{ $duty->exam->name ?? 'N/A' }}</td>
                  </tr>
                  <tr>
                    <th class="ps-4 py-3">Room</th>
                    <td class="py-3">
                      {{ $duty->room->name ?? 'N/A' }}
                      @if($duty->room && $duty->room->capacity)
                      <small class="text-muted">(Capacity: {{ $duty->room->capacity }})</small>
                      @endif
                    </td>
                  </tr>
                  <tr class="bg-light">
                    <th class="ps-4 py-3">Date</th>
                    <td class="py-3">{{ \Carbon\Carbon::parse($duty->date)->format('d M Y (l)') }}</td>
                  </tr>
                  <tr>
                    <th class="ps-4 py-3">Session</th>
                    <td class="py-3"><span class="badge bg-info text-capitalize">{{ $duty->session }}</span></td>
                  </tr>
                  <tr class="bg-light">
                    <th class="ps-4 py-3">Role</th>
                    <td class="py-3"><span class="text-capitalize fw-semibold">{{ str_replace('_', ' ', $duty->role) }}</span></td>
                  </tr>
                  <tr>
                    <th class="ps-4 py-3">Status</th>
                    <td class="py-3">
                      @if($duty->status === 'assigned')
                      <span class="badge bg-warning fs-6">Assigned</span>
                      @elseif($duty->status === 'completed')
                      <span class="badge bg-success fs-6">Completed</span>
                      @else
                      <span class="badge bg-secondary fs-6 text-capitalize">{{ $duty->status }}</span>
                      @endif
                    </td>
                  </tr>
                  <tr class="bg-light">
                    <th class="ps-4 py-3">Created</th>
                    <td class="py-3">{{ $duty->created_at ? $duty->created_at->format('d M Y, h:i A') : 'N/A' }}</td>
                  </tr>
                  <tr>
                    <th class="ps-4 py-3">Last Updated</th>
                    <td class="py-3">{{ $duty->updated_at ? $duty->updated_at->format('d M Y, h:i A') : 'N/A' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold"><i class="fas fa-cogs me-2 text-primary"></i>Actions</h5>
            </div>
            <div class="card-body">
              @if($duty->status === 'assigned')
              <form action="{{ route('admin.invigilation-duties.mark-completed', $duty->id) }}" method="POST" class="mb-3">
                @csrf
                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Mark this duty as completed?')">
                  <i class="fas fa-check-circle me-2"></i>Mark as Completed
                </button>
              </form>
              @endif
              <a href="{{ route('admin.invigilation-duties.edit', $duty->id) }}" class="btn btn-primary w-100 mb-3">
                <i class="fas fa-edit me-2"></i>Edit Duty
              </a>
              <form action="{{ route('admin.invigilation-duties.destroy', $duty->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Are you sure you want to delete this duty?')">
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