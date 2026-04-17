@include('includes.header')
<div class="wrapper">
  @include('coe.sidebar')


  <div class="p-4 mb-4 bg-gradient-primary text-white rounded-3 shadow">
    <div class="container-fluid py-3">
      <h1 class="display-6 fw-bold">Exam Registration Details</h1>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.exam-registrations.index') }}" class="text-white">Exam Registrations</a></li>
          <li class="breadcrumb-item active text-white" aria-current="page">Details</li>
        </ol>
      </nav>
    </div>
  </div>

  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-10 mx-auto">
        <!-- Action Buttons -->
        <div class="mb-3 d-flex justify-content-between">
          <a href="{{ route('admin.exam-registrations.index') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to List
          </a>
          <div>
            <a href="{{ route('admin.exam-registrations.edit', $registration->id) }}" class="btn btn-primary">
              <i class="fa fa-edit"></i> Edit Registration
            </a>
            <button onclick="window.print()" class="btn btn-info">
              <i class="fa fa-print"></i> Print
            </button>
          </div>
        </div>

        <!-- Registration Details Card -->
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
              <i class="fa fa-file-text"></i> Registration Information
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="text-muted">Registration Number</label>
                <h6 class="fw-bold">{{ $registration->registration_number ?? 'N/A' }}</h6>
              </div>
              <div class="col-md-6 mb-3">
                <label class="text-muted">Registration Date</label>
                <h6 class="fw-bold">
                  {{ $registration->registration_date ? \Carbon\Carbon::parse($registration->registration_date)->format('d M Y') : 'N/A' }}
                </h6>
              </div>
              <div class="col-md-6 mb-3">
                <label class="text-muted">Status</label>
                <div>
                  @if($registration->status == 'pending')
                  <span class="badge bg-warning fs-6">Pending</span>
                  @elseif($registration->status == 'approved')
                  <span class="badge bg-success fs-6">Approved</span>
                  @elseif($registration->status == 'rejected')
                  <span class="badge bg-danger fs-6">Rejected</span>
                  @else
                  <span class="badge bg-secondary fs-6">Cancelled</span>
                  @endif
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="text-muted">Registration Type</label>
                <div>
                  @if($registration->is_backlog)
                  <span class="badge bg-warning fs-6">Backlog</span>
                  @else
                  <span class="badge bg-info fs-6">Regular</span>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Student Details Card -->
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-info text-white">
            <h5 class="mb-0">
              <i class="fa fa-user"></i> Student Information
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="text-muted">Student Name</label>
                <h6 class="fw-bold text-capitalize">
                  {{ $registration->student->first_name ?? '' }}
                  {{ $registration->student->middle_name ?? '' }}
                  {{ $registration->student->last_name ?? '' }}
                </h6>
              </div>
              <div class="col-md-3 mb-3">
                <label class="text-muted">Registration No</label>
                <h6 class="fw-bold">{{ $registration->student->register_no ?? 'N/A' }}</h6>
              </div>
              <div class="col-md-3 mb-3">
                <label class="text-muted">Roll No</label>
                <h6 class="fw-bold">{{ $registration->student->roll_no ?? 'N/A' }}</h6>
              </div>
              <div class="col-md-6 mb-3">
                <label class="text-muted">Email</label>
                <h6>{{ $registration->student->mail_id ?? 'N/A' }}</h6>
              </div>
              <div class="col-md-6 mb-3">
                <label class="text-muted">Mobile</label>
                <h6>{{ $registration->student->mobile_no ?? 'N/A' }}</h6>
              </div>
              <div class="col-md-6 mb-3">
                <label class="text-muted">Campus</label>
                <h6 class="fw-bold">{{ $registration->student->campusmaster->name ?? 'N/A' }}</h6>
              </div>
              <div class="col-md-6 mb-3">
                <label class="text-muted">Batch</label>
                <h6 class="fw-bold">{{ $registration->student->batchmaster->batch_name ?? 'N/A' }}</h6>
              </div>
              <div class="col-md-6 mb-3">
                <label class="text-muted">Department</label>
                <h6 class="fw-bold">{{ $registration->student->deptmaster->name ?? 'N/A' }}</h6>
              </div>
            </div>
          </div>
        </div>

        <!-- Exam Details Card -->
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-success text-white">
            <h5 class="mb-0">
              <i class="fa fa-graduation-cap"></i> Exam Information
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="text-muted">Exam Name</label>
                <h6 class="fw-bold">{{ $registration->exam->name ?? 'N/A' }}</h6>
              </div>
              <div class="col-md-6 mb-3">
                <label class="text-muted">Exam Type</label>
                <h6>
                  <span class="badge bg-secondary">{{ $registration->exam->exam_type ?? 'N/A' }}</span>
                </h6>
              </div>
              <div class="col-md-6 mb-3">
                <label class="text-muted">Exam Date</label>
                <h6 class="fw-bold">
                  {{ $registration->exam->exam_date ? \Carbon\Carbon::parse($registration->exam->exam_date)->format('d M Y') : 'N/A' }}
                </h6>
              </div>
              <div class="col-md-6 mb-3">
                <label class="text-muted">Semester</label>
                <h6 class="fw-bold">
                  {{ $registration->semester_id ? 'Semester ' . $registration->semester_id : 'N/A' }}
                </h6>
              </div>
              @if($registration->exam->program)
              <div class="col-md-12 mb-3">
                <label class="text-muted">Program</label>
                <h6 class="fw-bold">{{ $registration->exam->program->name ?? 'N/A' }}</h6>
              </div>
              @endif
            </div>
          </div>
        </div>

        <!-- Payment Details Card -->
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">
              <i class="fa fa-money"></i> Payment Information
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-4 mb-3">
                <label class="text-muted">Registration Fee</label>
                <h6 class="fw-bold text-success">₹{{ number_format($registration->registration_fee, 2) }}</h6>
              </div>
              <div class="col-md-4 mb-3">
                <label class="text-muted">Payment Status</label>
                <div>
                  @if($registration->fee_paid)
                  <span class="badge bg-success fs-6"><i class="fa fa-check"></i> Paid</span>
                  @else
                  <span class="badge bg-danger fs-6"><i class="fa fa-times"></i> Unpaid</span>
                  @endif
                </div>
              </div>
              <div class="col-md-4 mb-3">
                <label class="text-muted">Payment Date</label>
                <h6>
                  {{ $registration->payment_date ? \Carbon\Carbon::parse($registration->payment_date)->format('d M Y') : 'N/A' }}
                </h6>
              </div>
              @if($registration->payment_reference)
              <div class="col-md-12 mb-3">
                <label class="text-muted">Payment Reference</label>
                <h6 class="fw-bold">{{ $registration->payment_reference }}</h6>
              </div>
              @endif
            </div>
          </div>
        </div>

        <!-- Approval & Remarks Card -->
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
              <i class="fa fa-info-circle"></i> Additional Information
            </h5>
          </div>
          <div class="card-body">
            @if($registration->approved_at)
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="text-muted">Approved Date</label>
                <h6 class="fw-bold">
                  {{ \Carbon\Carbon::parse($registration->approved_at)->format('d M Y, h:i A') }}
                </h6>
              </div>
            </div>
            @endif

            @if($registration->remarks)
            <div class="row">
              <div class="col-md-12">
                <label class="text-muted">Remarks</label>
                <div class="alert alert-light">
                  {{ $registration->remarks }}
                </div>
              </div>
            </div>
            @else
            <p class="text-muted">No remarks available</p>
            @endif

            <div class="row mt-3">
              <div class="col-md-6">
                <label class="text-muted">Created At</label>
                <p>{{ \Carbon\Carbon::parse($registration->created_at)->format('d M Y, h:i A') }}</p>
              </div>
              <div class="col-md-6">
                <label class="text-muted">Last Updated</label>
                <p>{{ \Carbon\Carbon::parse($registration->updated_at)->format('d M Y, h:i A') }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@include('includes.footer')

<style>
  @media print {

    .btn,
    nav,
    .breadcrumb {
      display: none !important;
    }
  }
</style>