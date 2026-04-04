@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Moderation</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.moderation-duties.index') }}">Moderation Duties</a></li>
            <li class="breadcrumb-item active" aria-current="page">View Duty</li>
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
                  <h3 class="text-dark fw-bold mb-2"><i class="fas fa-eye me-2"></i>Moderation Duty Details</h3>
                  <p class="text-muted mb-0">Duty #{{ $duty->id }}</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('admin.moderation-duties.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-8">
          <div class="card shadow-sm border-0">
            <div class="card-body p-0">
              <table class="table table-borderless mb-0">
                <tbody>
                  <tr class="bg-light">
                    <th class="ps-4 py-3" style="width: 200px;">Faculty</th>
                    <td class="py-3">
                      <span class="fw-semibold">{{ $duty->faculty->FIRST_NAME ?? '' }} {{ $duty->faculty->LAST_NAME ?? '' }}</span>
                      @if($duty->faculty && $duty->faculty->DEPARTMENT)
                      <br><small class="text-muted">{{ $duty->faculty->DEPARTMENT }}</small>
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <th class="ps-4 py-3">Subject</th>
                    <td class="py-3">
                      <span class="fw-semibold">{{ $duty->subject->subject_code ?? 'N/A' }}</span>
                      @if($duty->subject && $duty->subject->name)
                      <br><small class="text-muted">{{ $duty->subject->name }}</small>
                      @endif
                    </td>
                  </tr>
                  <tr class="bg-light">
                    <th class="ps-4 py-3">Exam</th>
                    <td class="py-3">{{ $duty->exam->name ?? 'N/A' }}</td>
                  </tr>
                  <tr>
                    <th class="ps-4 py-3">Moderation Type</th>
                    <td class="py-3">
                      <span class="badge {{ $duty->moderation_type == 'internal' ? 'bg-info' : 'bg-secondary' }} fs-6">
                        {{ ucfirst($duty->moderation_type) }}
                      </span>
                    </td>
                  </tr>
                  <tr class="bg-light">
                    <th class="ps-4 py-3">Status</th>
                    <td class="py-3">
                      @if($duty->status == 'completed')
                      <span class="badge bg-success fs-6">Completed</span>
                      @elseif($duty->status == 'pending')
                      <span class="badge bg-warning text-dark fs-6">Pending</span>
                      @else
                      <span class="badge bg-secondary fs-6">{{ ucfirst($duty->status) }}</span>
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <th class="ps-4 py-3">Assigned On</th>
                    <td class="py-3">{{ $duty->created_at->format('d M Y, h:i A') }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-light fw-semibold">
              <i class="fas fa-cog me-2"></i>Actions
            </div>
            <div class="card-body">
              <div class="d-grid gap-2">
                <a href="{{ route('admin.moderation-duties.edit', $duty->id) }}" class="btn btn-warning">
                  <i class="fas fa-edit me-2"></i>Edit Duty
                </a>
                @if($duty->status != 'completed')
                <form action="{{ route('admin.moderation-duties.mark-completed', $duty->id) }}" method="POST">
                  @csrf
                  <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-check me-2"></i>Mark Completed
                  </button>
                </form>
                @endif
                <form action="{{ route('admin.moderation-duties.destroy', $duty->id) }}" method="POST"
                  onsubmit="return confirm('Are you sure you want to delete this duty?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-outline-danger w-100">
                    <i class="fas fa-trash me-2"></i>Delete Duty
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')