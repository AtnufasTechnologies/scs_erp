@include('includes.header')

<div class="wrapper">
  @include('tpo.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Placement Program</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Student Opt-Ins</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
          <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-user-check me-2 text-primary"></i>Student Training & Placement Opt-Ins</h4>
            <p class="mb-0 text-muted">Single page to review submitted student opt-ins and approve eligible applications.</p>
          </div>
          <a href="{{ route('tpo.training-placement.analytics') }}" class="btn btn-outline-primary">
            <i class="fas fa-chart-line me-1"></i>Training Analytics
          </a>
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent fw-bold">Master Form For Students (Download Template)</div>
        <div class="card-body">
          <p class="text-muted mb-3">Upload the official Training & Placement form that students will download, fill and submit.</p>

          @if(!empty($masterTemplate?->file_path))
          <div class="alert alert-info d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <div class="fw-semibold">Current Template: {{ $masterTemplate->title ?: 'Training and Placement Opt-In Form' }}</div>
              <small>Uploaded at {{ optional($masterTemplate->created_at)->format('d M Y h:i A') }}</small>
            </div>
            <a href="{{ Storage::disk('s3')->url($masterTemplate->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
              <i class="fas fa-download me-1"></i>Download Current Template
            </a>
          </div>
          @endif

          <form action="{{ route('tpo.training-placement.student-opt-in-forms.template.store') }}" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-4">
              <label class="form-label fw-semibold mb-1">Template Title (Optional)</label>
              <input type="text" name="template_title" class="form-control" placeholder="Training & Placement Opt-In Form">
            </div>
            <div class="col-md-5">
              <label class="form-label fw-semibold mb-1">Upload Template File Allowed: PDF, DOC, DOCX. Max 10MB</label>
              <input type="file" name="template_file" class="form-control" accept=".pdf,.doc,.docx" required>

            </div>
            <div class="col-md-3 d-flex">
              <button type="submit" class="btn btn-success w-100">
                <i class="fas fa-upload me-1"></i>{{ !empty($masterTemplate?->file_path) ? 'Replace Template' : 'Upload Template' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      @if(!$hasOptInTable)
      <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-1"></i>
        Opt-in form storage table is missing. Run the latest placement migration to enable uploads.
      </div>
      @endif

      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
          <form method="GET" action="{{ route('tpo.training-placement.student-opt-in-forms.index') }}" class="row g-2 align-items-end">
            <div class="col-md-10">
              <label class="form-label fw-semibold mb-1">Search Student</label>
              <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search by name, roll, register, email, program, pathway, track, combo">
            </div>
            <div class="col-md-2 d-flex gap-2">
              <button type="submit" class="btn btn-primary w-100">Search</button>
              @if($search !== '')
              <a href="{{ route('tpo.training-placement.student-opt-in-forms.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
              @endif
            </div>
          </form>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6 col-lg-3">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <div class="text-muted small">Students Opted For Program</div>
              <h4 class="mb-0 fw-bold text-primary">{{ (int) ($totalOptedStudents ?? 0) }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <div class="text-muted small">Enrolled Program Groups</div>
              <h4 class="mb-0 fw-bold text-success">{{ (int) ($optedProgramsCount ?? 0) }}</h4>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent fw-bold">Opted Students By Enrolled Program</div>
        <div class="card-body p-0">
          @if(!empty($optedProgramAnalytics) && $optedProgramAnalytics->isNotEmpty())
          <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
              <thead>
                <tr>
                  <th class="px-3">Program Code</th>
                  <th>Program Name</th>
                  <th class="text-end pe-3">Opted Students</th>
                </tr>
              </thead>
              <tbody>
                @foreach($optedProgramAnalytics as $programRow)
                <tr>
                  <td class="px-3 fw-semibold">{{ $programRow['program_code'] }}</td>
                  <td>{{ $programRow['program_name'] }}</td>
                  <td class="text-end pe-3">{{ (int) $programRow['opted_count'] }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @else
          <div class="p-3 text-muted">No opted student records found for analytics.</div>
          @endif
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent fw-bold">Applied Students List</div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table mb-0 align-middle">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Student</th>
                  <th>Roll / Register</th>
                  <th>Enrolled Program / Campus</th>
                  <th>Current Year / Semester</th>
                  <th>Current Status</th>
                  <th class="text-end">Review Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($students as $index => $student)
                @php
                $studentName = trim((string) (($student->first_name ?? '') . ' ' . ($student->last_name ?? '')));
                if ($studentName === '') {
                $studentName = 'N/A';
                }
                $hasForm = !empty($student->form_file_path ?? '');
                $approvalStatus = strtolower((string) ($student->approval_status ?? 'in_review'));

                $programCodeParts = [];
                $programCodeParts[] = !empty($student->new_program_id) ? ('PRG-' . $student->new_program_id) : 'PRG-NA';
                if (!empty($student->academic_pathway_id)) {
                $programCodeParts[] = 'AP-' . $student->academic_pathway_id;
                }
                if (!empty($student->degree_track_id)) {
                $programCodeParts[] = 'DT-' . $student->degree_track_id;
                }
                if (!empty($student->selected_combo_id)) {
                $programCodeParts[] = !empty($student->selected_combo_code) ? $student->selected_combo_code : ('COMBO-' . $student->selected_combo_id);
                }
                $programCodeDisplay = implode(' / ', $programCodeParts);

                $programNameParts = [];
                $programNameParts[] = !empty($student->enrolled_program_name_base) ? $student->enrolled_program_name_base : (!empty($student->new_program_id) ? ('Program #' . $student->new_program_id) : 'Program N/A');
                if (!empty($student->academic_pathway_id)) {
                $programNameParts[] = 'Pathway: ' . (!empty($student->academic_pathway_name) ? $student->academic_pathway_name : ('#' . $student->academic_pathway_id));
                }
                if (!empty($student->degree_track_id)) {
                $programNameParts[] = 'Track: ' . (!empty($student->degree_track_name) ? $student->degree_track_name : ('#' . $student->degree_track_id));
                }
                if (!empty($student->selected_combo_id)) {
                $comboName = !empty($student->selected_combo_title) ? $student->selected_combo_title : (!empty($student->selected_combo_name) ? $student->selected_combo_name : ('#' . $student->selected_combo_id));
                $programNameParts[] = 'Combo: ' . $comboName;
                }
                $programNameDisplay = implode(' | ', $programNameParts);
                @endphp
                <tr>
                  <td>{{ $students->firstItem() + $index }}</td>
                  <td>
                    <div class="fw-semibold">{{ $studentName }}</div>
                    <div class="text-muted small">{{ $student->mail_id ?: ($student->user_email ?: 'N/A') }}</div>
                  </td>
                  <td>
                    <div class="small">Roll: {{ $student->roll_no ?: 'N/A' }}</div>
                    <div class="small text-muted">Reg: {{ $student->register_no ?: 'N/A' }}</div>
                  </td>
                  <td>
                    <div class="small">{{ $programCodeDisplay }}</div>
                    <div class="small text-muted">{{ $programNameDisplay }}</div>
                    <div class="small text-muted">{{ $student->campus_name ?: 'N/A' }}</div>
                  </td>
                  <td>
                    <div class="small">Year: {{ !empty($student->current_year) ? 'Year ' . $student->current_year : 'N/A' }}</div>
                    <div class="small text-muted">Semester: {{ $student->current_semester_title ?: (!empty($student->current_semester_id) ? ('Semester ' . $student->current_semester_id) : 'N/A') }}</div>
                  </td>
                  <td>
                    @if($hasForm)
                    @if(($hasApprovalColumns ?? false) && $approvalStatus === 'approved')
                    <span class="badge bg-success">Approved</span>
                    @elseif(($hasApprovalColumns ?? false) && $approvalStatus === 'rejected')
                    <span class="badge bg-danger">Rejected</span>
                    @else
                    <span class="badge bg-warning text-dark">In Review</span>
                    @endif
                    <div class="small text-muted mt-1">{{ !empty($student->opted_at) ? \Carbon\Carbon::parse($student->opted_at)->format('d M Y h:i A') : 'Date unavailable' }}</div>
                    @if(($hasApprovalColumns ?? false) && !empty($student->approved_at))
                    <div class="small text-muted">Approved on {{ \Carbon\Carbon::parse($student->approved_at)->format('d M Y h:i A') }}</div>
                    @endif
                    @if(($hasApprovalColumns ?? false) && !empty($student->rejected_at))
                    <div class="small text-muted">Rejected on {{ \Carbon\Carbon::parse($student->rejected_at)->format('d M Y h:i A') }}</div>
                    @endif
                    @if(($hasApprovalColumns ?? false) && !empty($student->rejection_reason))
                    <div class="small text-danger mt-1">Reason: {{ $student->rejection_reason }}</div>
                    @endif
                    <div class="mt-1">
                      <a href="{{ Storage::disk('s3')->url($student->form_file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye me-1"></i>View
                      </a>
                    </div>
                    @else
                    <span class="badge bg-secondary">Not Uploaded</span>
                    @endif
                  </td>
                  <td class="text-end">
                    <div class="d-inline-flex align-items-start flex-column gap-2" style="min-width:230px;">
                      @if($hasForm && ($hasApprovalColumns ?? false) && $approvalStatus !== 'approved')
                      <form action="{{ route('tpo.training-placement.student-opt-in-forms.approve', $student->student_id) }}" method="POST" class="d-inline-block text-start">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success">
                          <i class="fas fa-check me-1"></i>Approve
                        </button>
                      </form>
                      <form action="{{ route('tpo.training-placement.student-opt-in-forms.reject', $student->student_id) }}" method="POST" class="d-inline-block text-start" style="width:100%;">
                        @csrf
                        <textarea name="rejection_reason" class="form-control form-control-sm mb-2" rows="2" maxlength="1000" placeholder="Enter rejection reason" required></textarea>
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                          <i class="fas fa-times me-1"></i>Reject
                        </button>
                      </form>
                      @endif
                      @if(!empty($student->user_id))
                      <a href="{{ route('tpo.training-placement.opted-students.analysis', $student->user_id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-file-alt me-1"></i>View Training Analysis
                      </a>
                      @endif
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="7" class="text-center text-muted py-4">No applied students found.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        @if($students->hasPages())
        <div class="card-footer bg-transparent">
          {{ $students->links() }}
        </div>
        @endif
      </div>
    </div>
  </main>
</div>

@include('includes.footer')