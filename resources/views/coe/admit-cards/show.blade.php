@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Admit Cards</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.admit-cards.index') }}">Admit Cards</a></li>
            <li class="breadcrumb-item active" aria-current="page">Admit Card Details</li>
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
                    <i class="fas fa-id-card me-2"></i>{{ $registration->student->full_name ?? 'Student' }}
                  </h3>
                  <p class="text-white-50 mb-0">
                    <span class="badge bg-light text-dark me-2">{{ $registration->student->register_no ?? 'N/A' }}</span>
                    @if($registration->seatingAllocation && $registration->dummyNumber)
                    <span class="badge bg-success"><i class="fa fa-check-circle"></i> Ready</span>
                    @else
                    <span class="badge bg-warning"><i class="fa fa-clock"></i> Pending</span>
                    @endif
                  </p>
                </div>
                <div class="col-md-4 text-md-end">
                  @if($registration->seatingAllocation && $registration->dummyNumber)
                  <a href="{{ route('coe.admit-cards.download', $registration->id) }}" class="btn btn-light me-2" target="_blank">
                    <i class="fa fa-download me-1"></i>Download PDF
                  </a>
                  @endif
                  <a href="{{ route('coe.admit-cards.index') }}" class="btn btn-outline-light">
                    <i class="fa fa-arrow-left me-1"></i>Back
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      @if(!$registration->seatingAllocation || !$registration->dummyNumber)
      <div class="alert alert-warning border-0 shadow-sm" role="alert">
        <div class="d-flex align-items-center">
          <i class="fas fa-exclamation-triangle me-3" style="font-size: 2rem;"></i>
          <div>
            <h6 class="mb-1"><strong>Incomplete Information</strong></h6>
            <p class="mb-0">
              @if(!$registration->seatingAllocation) Seating not allocated. @endif
              @if(!$registration->dummyNumber) Dummy number not assigned. @endif
              Admit card cannot be generated until all information is complete.
            </p>
          </div>
        </div>
      </div>
      @endif

      <!-- Details Section -->
      <div class="row">
        <!-- Student Information -->
        <div class="col-md-6 mb-4">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-primary text-white py-3">
              <h6 class="mb-0 fw-bold"><i class="fas fa-user me-2"></i>Student Information</h6>
            </div>
            <div class="card-body">
              <table class="table table-sm table-borderless mb-0">
                <tr>
                  <th width="40%">Name:</th>
                  <td>{{ $registration->student->full_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <th>Registration No.:</th>
                  <td><strong>{{ $registration->student->register_no ?? 'N/A' }}</strong></td>
                </tr>
                <tr>
                  <th>Roll No.:</th>
                  <td>{{ $registration->student->roll_no ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <th>Programme:</th>
                  <td>{{ $registration->student->programgroup->programInfo->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <th>Department:</th>
                  <td>{{ $registration->student->deptmaster->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <th>Email:</th>
                  <td>{{ $registration->student->mail_id ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <th>Phone:</th>
                  <td>{{ $registration->student->mobile_no ?? 'N/A' }}</td>
                </tr>
              </table>
            </div>
          </div>
        </div>

        <!-- Exam Information -->
        <div class="col-md-6 mb-4">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-success text-white py-3">
              <h6 class="mb-0 fw-bold"><i class="fas fa-clipboard-check me-2"></i>Exam Information</h6>
            </div>
            <div class="card-body">
              <table class="table table-sm table-borderless mb-0">
                <tr>
                  <th width="40%">Exam:</th>
                  <td>{{ $registration->examSession->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <th>Type:</th>
                  <td>{{ $registration->examSession->program_type ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <th>Semester:</th>
                  <td><span class="badge bg-purple">Semester {{ $registration->examSession->semester ?? 'N/A' }}</span></td>
                </tr>
                <tr>
                  <th>Exam Date:</th>
                  <td>{{ $registration->examSession->start_date ? \Carbon\Carbon::parse($registration->examSession->start_date)->format('d M Y') : 'N/A' }}</td>
                </tr>
                <tr>
                  <th>Registration Date:</th>
                  <td>{{ $registration->created_at->format('d M Y') }}</td>
                </tr>
                <tr>
                  <th>Status:</th>
                  <td>
                    @if($registration->status === 'approved')
                    <span class="badge bg-success">Approved</span>
                    @else
                    <span class="badge bg-warning">{{ ucfirst($registration->status) }}</span>
                    @endif
                  </td>
                </tr>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Seating & Dummy Number -->
        <div class="col-md-6 mb-4">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-info text-white py-3">
              <h6 class="mb-0 fw-bold"><i class="fas fa-chair me-2"></i>Seating Allocation & Dummy Number</h6>
            </div>
            <div class="card-body">
              @if($registration->seatingAllocation)
              <table class="table table-sm table-borderless">
                <tr>
                  <th width="40%">Room:</th>
                  <td><span class="badge bg-info">Room {{ $registration->seatingAllocation->room_no ?? 'N/A' }}</span></td>
                </tr>
                <tr>
                  <th>Seat Number:</th>
                  <td><strong class="text-primary fs-5">{{ $registration->seatingAllocation->seat_no }}</strong></td>
                </tr>
                <tr>
                  <th>Allocated On:</th>
                  <td><small>{{ $registration->seatingAllocation->created_at->format('d M Y h:i A') }}</small></td>
                </tr>
              </table>
              @else
              <div class="alert alert-warning border-0 mb-3">
                <i class="fas fa-exclamation-circle me-2"></i>Seating not allocated yet.
              </div>
              @endif

              @if($registration->dummyNumber)
              <hr>
              <table class="table table-sm table-borderless mb-0">
                <tr>
                  <th width="40%">Dummy Number:</th>
                  <td><strong class="text-success fs-5">{{ $registration->dummyNumber->dummy_number }}</strong></td>
                </tr>
                <tr>
                  <th>Assigned On:</th>
                  <td><small>{{ $registration->dummyNumber->created_at->format('d M Y h:i A') }}</small></td>
                </tr>
              </table>
              @else
              @if($registration->seatingAllocation)
              <hr>@endif
              <div class="alert alert-warning border-0 mb-0">
                <i class="fas fa-exclamation-circle me-2"></i>Dummy number not assigned yet.
              </div>
              @endif
            </div>
          </div>
        </div>

        <!-- Registered Subjects -->
        <div class="col-md-6 mb-4">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-warning text-dark py-3">
              <h6 class="mb-0 fw-bold"><i class="fas fa-book me-2"></i>Registered Subjects</h6>
            </div>
            <div class="card-body">
              @if($registration->subjects && $registration->subjects->count() > 0)
              <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                  <thead class="table-light">
                    <tr>
                      <th width="10%">#</th>
                      <th width="25%">Code</th>
                      <th width="50%">Subject Name</th>
                      <th width="15%">Credits</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($registration->subjects as $index => $subject)
                    <tr>
                      <td>{{ $index + 1 }}</td>
                      <td><strong>{{ $subject->subject_code }}</strong></td>
                      <td>{{ $subject->name }}</td>
                      <td><span class="badge bg-secondary">{{ $subject->credits ?? 'N/A' }}</span></td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              @else
              <div class="alert alert-warning border-0 mb-0">
                <i class="fas fa-exclamation-circle me-2"></i>No subjects registered.
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