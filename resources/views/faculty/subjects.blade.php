@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Subjects</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">My Subjects</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid mt-4">
      <!-- Header Card -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card shadow-sm border-0 bg-gradient-primary text-white">
            <div class="card-body py-4">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <h4 class="mb-2 fw-bold text-white"><i class="fas fa-book-open me-2"></i>My Assigned Subjects</h4>
                  <p class="mb-0 text-white-50">View and track your teaching assignments organized by batch and semester</p>
                </div>
                <div class="text-end">
                  <div class="display-6 text-white fw-bold">{{ count($batchWiseSubjects) }}</div>
                  <small class="text-white-50">Batches</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Subjects by Batch -->
      @forelse($batchWiseSubjects as $batchName => $subjects)
      <div class="row mb-4">
        <div class="col-12">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
              <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold">
                  <i class="fas fa-users text-primary me-2"></i>
                  Batch: {{ $batchName }}
                </h5>
                <div class="d-flex gap-2">
                  <span class="badge bg-light-info text-info px-3 py-2">
                    <i class="fas fa-book me-1"></i>{{ count($subjects) }} Subject{{ count($subjects) > 1 ? 's' : '' }}
                  </span>
                </div>
              </div>
            </div>
            <div class="card-body p-0">
              @php
              // Group by semester
              $semesterGroups = $subjects->groupBy(function($item) {
              return $item->syllabus->semestermaster->id ?? 0;
              });
              @endphp

              @foreach($semesterGroups as $semesterId => $semesterSubjects)
              @php
              $firstSubject = $semesterSubjects->first();
              $semesterName = $firstSubject->syllabus->semestermaster->title ?? 'Unknown Semester';
              @endphp

              <!-- Semester Section -->
              <div class="semester-section border-bottom">
                <div class="semester-header bg-light px-4 py-3">
                  <h6 class="mb-0 text-primary">
                    <i class="fas fa-calendar-alt me-2"></i>{{ $semesterName }}
                  </h6>
                </div>

                <!-- Courses in this Semester -->
                <div class="accordion accordion-flush" id="accordion{{ str_replace(' ', '', $batchName) }}{{ $semesterId }}">
                  @foreach($semesterSubjects as $index => $subjectData)
                  @php
                  $syllabus = $subjectData->syllabus;
                  $subject = $syllabus->subject ?? null;
                  $courseMaster = $syllabus->courseLink->courseMaster ?? null;
                  $courseType = $courseMaster->coursetypemaster ?? null;
                  $syllabusUnits = $syllabus->syllabusunits ?? collect();

                  $completedUnits = $syllabusUnits->where('is_completed', 1)->count();
                  $totalUnits = $syllabusUnits->count();
                  $completionPercentage = $totalUnits > 0 ? round(($completedUnits / $totalUnits) * 100) : 0;
                  @endphp

                  <div class="accordion-item border-0">
                    <h2 class="accordion-header" id="heading{{ str_replace(' ', '', $batchName) }}{{ $semesterId }}{{ $index }}">
                      <button class="accordion-button collapsed px-4 py-3" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapse{{ str_replace(' ', '', $batchName) }}{{ $semesterId }}{{ $index }}"
                        aria-expanded="false">
                        <div class="d-flex align-items-center justify-content-between w-100 me-3">
                          <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-3 mb-2">
                              @if($courseType)
                              <span class="badge bg-primary">{{ $courseType->title }}</span>
                              @endif
                              <h6 class="mb-0 fw-bold">{{ $subject->title ?? 'N/A' }}</h6>
                            </div>
                            <div class="d-flex align-items-center gap-3 text-muted">
                              <small>
                                <i class="fas fa-code me-1"></i>{{ $courseMaster->course_code ?? 'N/A' }}
                              </small>
                              <small class="text-muted">|</small>
                              <small>{{ $courseMaster->course_title ?? 'N/A' }}</small>
                            </div>
                          </div>
                          <div class="d-flex align-items-center gap-3">
                            @if($courseMaster)
                            <div class="text-center">
                              <div class="badge bg-light-warning text-warning px-3 py-2">
                                <i class="fas fa-star me-1"></i>{{ $courseMaster->credits ?? 0 }} Credits
                              </div>
                            </div>
                            <div class="text-center">
                              <small class="text-muted d-block">Marks</small>
                              <strong class="text-primary">{{ $courseMaster->internal ?? 0 }}</strong> /
                              <strong class="text-success">{{ $courseMaster->external ?? 0 }}</strong>
                            </div>
                            <div class="text-center">
                              <small class="text-muted d-block">Hours</small>
                              <strong>{{ $courseMaster->total_alloted_hours ?? 0 }}</strong>
                            </div>
                            @endif
                            @if($totalUnits > 0)
                            <div class="text-center">
                              <div class="progress" style="width: 80px; height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                  style="width: {{ $completionPercentage }}%"
                                  aria-valuenow="{{ $completionPercentage }}"
                                  aria-valuemin="0" aria-valuemax="100"></div>
                              </div>
                              <small class="text-muted">{{ $completedUnits }}/{{ $totalUnits }} units</small>
                            </div>
                            @endif
                          </div>
                        </div>
                      </button>
                    </h2>
                    <div id="collapse{{ str_replace(' ', '', $batchName) }}{{ $semesterId }}{{ $index }}"
                      class="accordion-collapse collapse"
                      data-bs-parent="#accordion{{ str_replace(' ', '', $batchName) }}{{ $semesterId }}">
                      <div class="accordion-body px-4 py-4 bg-light">
                        @if($syllabusUnits->count() > 0)
                        <!-- Instructional Units Table -->
                        <div class="card border-0 shadow-sm">
                          <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 text-primary">
                              <i class="fas fa-list-ul me-2"></i>Instructional Objectives
                            </h6>
                            <div class="d-flex gap-2">
                              <span class="scs-stat-pill scs-stat-success"><i class="fas fa-check-circle me-1"></i>{{ $completedUnits }} Done</span>
                              <span class="scs-stat-pill scs-stat-warning"><i class="fas fa-clock me-1"></i>{{ $totalUnits - $completedUnits }} Pending</span>
                              <span class="scs-stat-pill scs-stat-primary">{{ $completionPercentage }}%</span>
                            </div>
                          </div>
                          <div class="card-body p-0">
                            <div class="table-responsive">
                              <table class="table scs-unit-table mb-0 align-middle">
                                <thead>
                                  <tr>
                                    <th class="text-center" style="width:4%;">#</th>
                                    <th style="width:38%;">Instructional Objective</th>
                                    <th class="text-center" style="width:16%;">Bloom's Level</th>
                                    <th class="text-center" style="width:10%;">Status</th>
                                    <th class="text-center" style="width:20%;">Tools</th>
                                    <th class="text-center" style="width:12%;">Mark Done</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  @foreach($syllabusUnits as $unit)
                                  @php
                                  $resourceCount = App\Models\LearningResource::where('syllabus_subunit_id', $unit->id)->count();
                                  $questionCount = App\Models\QuestionBank::where('syllabus_subunit_id', $unit->id)->count();
                                  @endphp
                                  <tr class="scs-unit-row {{ $unit->is_completed ? 'scs-row-done' : '' }}">
                                    <td class="text-center">
                                      <span class="scs-serial">{{ $loop->iteration }}</span>
                                    </td>
                                    <td>
                                      <div class="d-flex align-items-start gap-2">
                                        <span class="scs-unit-dot {{ $unit->is_completed ? 'dot-done' : 'dot-pending' }} mt-1"></span>
                                        <span class="scs-unit-title">{{ $unit->csoSubunit->title ?? 'N/A' }}</span>
                                      </div>
                                    </td>
                                    <td class="text-center">
                                      @if($unit->csoSubunit && $unit->csoSubunit->taxomonylevel)
                                      <span class="scs-bloom-badge scs-bloom-{{ strtolower($unit->csoSubunit->taxomonylevel->shortname) }}">
                                        <strong>{{ $unit->csoSubunit->taxomonylevel->shortname }}</strong>
                                        <span class="d-none d-xl-inline ms-1">{{ $unit->csoSubunit->taxomonylevel->fullname }}</span>
                                      </span>
                                      @else
                                      <span class="text-muted small">—</span>
                                      @endif
                                    </td>
                                    <td class="text-center">
                                      @if($unit->is_completed)
                                      <span class="scs-status-badge scs-status-done"><i class="fas fa-check me-1"></i>Done</span>
                                      @else
                                      <span class="scs-status-badge scs-status-pending"><i class="fas fa-hourglass-half me-1"></i>Pending</span>
                                      @endif
                                    </td>
                                    <td class="text-center">
                                      <div class="d-flex justify-content-center gap-2">
                                        <!-- Resources Button -->
                                        <button class="scs-tool-btn scs-tool-info"
                                          data-bs-toggle="modal"
                                          data-bs-target="#resourceModal{{ $unit->id }}"
                                          title="Learning Resources">
                                          <i class="fas fa-paperclip"></i>
                                          <span class="scs-count-badge {{ $resourceCount > 0 ? 'count-active' : 'count-empty' }}">{{ $resourceCount }}</span>
                                        </button>
                                        <!-- Questions Button -->
                                        <button class="scs-tool-btn scs-tool-purple"
                                          data-bs-toggle="modal"
                                          data-bs-target="#questionModal{{ $unit->id }}"
                                          title="Question Bank">
                                          <i class="fas fa-question-circle"></i>
                                          <span class="scs-count-badge {{ $questionCount > 0 ? 'count-active' : 'count-empty' }}">{{ $questionCount }}</span>
                                        </button>
                                      </div>
                                    </td>
                                    <td class="text-center">
                                      <a href="{{ route('faculty.toggle.subunitcompletion', $unit->id) }}"
                                        class="scs-toggle-btn {{ $unit->is_completed ? 'toggle-done' : 'toggle-pending' }}"
                                        title="{{ $unit->is_completed ? 'Mark as Pending' : 'Mark as Completed' }}"
                                        onclick="return confirm('Toggle completion status for this objective?')">
                                        <i class="fas {{ $unit->is_completed ? 'fa-times-circle' : 'fa-check-circle' }}"></i>
                                      </a>
                                    </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                              </table>
                            </div>
                          </div>
                        </div>

                        <!-- ===== MODALS ===== -->
                        @foreach($syllabusUnits as $unit)
                        @php
                        $resources = App\Models\LearningResource::where('syllabus_subunit_id', $unit->id)->with('uploader')->latest()->get();
                        $questions = App\Models\QuestionBank::where('syllabus_subunit_id', $unit->id)->with('cognitiveLevel')->latest()->get();
                        $cognitiveLevels = App\Models\CognitiveLevelMaster::all();
                        @endphp

                        <!-- Resources Modal -->
                        <div class="modal fade" id="resourceModal{{ $unit->id }}" tabindex="-1" aria-hidden="true">
                          <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content scs-modal">
                              <div class="modal-header scs-modal-header-teal">
                                <div>
                                  <h5 class="modal-title mb-0"><i class="fas fa-paperclip me-2"></i>Learning Resources</h5>
                                  <small class="opacity-75">{{ $unit->csoSubunit->title ?? 'Unit' }}</small>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                              </div>
                              <div class="modal-body p-0">
                                <!-- Upload Strip -->
                                <div class="scs-upload-strip">
                                  <form action="{{ route('faculty.resources.store') }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-end gap-3 flex-wrap">
                                    @csrf
                                    <input type="hidden" name="syllabus_subunit_id" value="{{ $unit->id }}">
                                    <div class="flex-grow-1">
                                      <label class="scs-form-label">Select Document</label>
                                      <input type="file" class="form-control form-control-sm" name="file" required accept=".pdf,.doc,.docx,.ppt,.pptx,.zip,.jpg,.jpeg,.png">
                                      <small class="text-muted">PDF, DOC, PPT, ZIP, Images — max 50 MB</small>
                                    </div>
                                    <button type="submit" class="btn scs-btn-teal btn-sm"><i class="fas fa-upload me-1"></i>Upload</button>
                                  </form>
                                </div>
                                <!-- Resource List -->
                                <div class="scs-list-body">
                                  @forelse($resources as $resource)
                                  <div class="scs-resource-item">
                                    <div class="scs-resource-icon">
                                      @php $ext = strtolower($resource->file_type ?? ''); @endphp
                                      @if($ext === 'pdf')<i class="fas fa-file-pdf text-danger fa-lg"></i>
                                      @elseif(in_array($ext, ['doc','docx']))<i class="fas fa-file-word text-primary fa-lg"></i>
                                      @elseif(in_array($ext, ['ppt','pptx']))<i class="fas fa-file-powerpoint text-warning fa-lg"></i>
                                      @elseif(in_array($ext, ['jpg','jpeg','png']))<i class="fas fa-file-image text-success fa-lg"></i>
                                      @else<i class="fas fa-file-alt text-secondary fa-lg"></i>
                                      @endif
                                    </div>
                                    <div class="scs-resource-info">
                                      <p class="scs-resource-name">{{ $resource->title }}</p>
                                      <div class="scs-resource-meta">
                                        <span><i class="fas fa-user"></i> {{ $resource->uploader->name ?? 'Unknown' }}</span>
                                        <span><i class="fas fa-calendar"></i> {{ $resource->created_at->format('d M Y') }}</span>
                                        <span><i class="fas fa-weight-hanging"></i> {{ $resource->formatted_file_size }}</span>
                                      </div>
                                    </div>
                                    <div class="scs-resource-actions">
                                      <a href="{{ $resource->file_path }}" target="_blank" class="scs-icon-btn scs-icon-primary" title="Download"><i class="fas fa-download"></i></a>
                                      <form action="{{ route('faculty.resources.destroy', $resource->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this resource?');">
                                        @csrf @method('DELETE')
                                        <button class="scs-icon-btn scs-icon-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                      </form>
                                    </div>
                                  </div>
                                  @empty
                                  <div class="scs-empty-state">
                                    <i class="fas fa-folder-open fa-2x mb-2 text-muted"></i>
                                    <p class="mb-0 text-muted">No documents uploaded yet</p>
                                  </div>
                                  @endforelse
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- Questions Modal -->
                        <div class="modal fade" id="questionModal{{ $unit->id }}" tabindex="-1" aria-hidden="true">
                          <div class="modal-dialog modal-xl modal-dialog-scrollable">
                            <div class="modal-content scs-modal">
                              <div class="modal-header scs-modal-header-purple">
                                <div>
                                  <h5 class="modal-title mb-0"><i class="fas fa-question-circle me-2"></i>Question Bank</h5>
                                  <small class="opacity-75">{{ $unit->csoSubunit->title ?? 'Unit' }}</small>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                  <span class="badge bg-white text-purple fw-bold px-3 py-2">{{ $questions->count() }} Question{{ $questions->count() !== 1 ? 's' : '' }}</span>
                                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                              </div>
                              <div class="modal-body p-0">
                                <div class="row g-0" style="min-height: 420px;">
                                  <!-- Add Question Panel -->
                                  <div class="col-12 col-lg-5 scs-qb-add-panel">
                                    <div class="scs-panel-header">
                                      <i class="fas fa-plus-circle me-2"></i>Add New Question
                                    </div>
                                    <div class="p-3">
                                      <form class="scs-qb-form" action="{{ route('faculty.questions.store') }}" method="POST" data-unit="{{ $unit->id }}">
                                        @csrf
                                        <input type="hidden" name="syllabus_subunit_id" value="{{ $unit->id }}">

                                        <div class="scs-form-group">
                                          <label class="scs-form-label">Question <span class="text-danger">*</span></label>
                                          <textarea class="scs-qb-editor" id="questionText{{ $unit->id }}" name="question_text"></textarea>
                                        </div>

                                        <div class="row g-2 mt-1">
                                          <div class="col-6">
                                            <div class="scs-form-group">
                                              <label class="scs-form-label">Marks <span class="text-danger">*</span></label>
                                              <input type="number" class="form-control form-control-sm" name="marks" value="2" min="1" max="100" required>
                                            </div>
                                          </div>
                                          <div class="col-6">
                                            <div class="scs-form-group">
                                              <label class="scs-form-label">Difficulty <span class="text-danger">*</span></label>
                                              <select class="form-select form-select-sm" name="difficulty" required>
                                                <option value="Easy">Easy</option>
                                                <option value="Medium" selected>Medium</option>
                                                <option value="Hard">Hard</option>
                                              </select>
                                            </div>
                                          </div>
                                          <div class="col-12">
                                            <div class="scs-form-group">
                                              <label class="scs-form-label">Cognitive Level (Bloom's Taxonomy) <span class="text-danger">*</span></label>
                                              <select class="form-select form-select-sm scs-bloom-level-select" name="cognitive_level_master_id" id="bloomLevel{{ $unit->id }}" required>
                                                <option value="">— Select a level —</option>
                                                @foreach($cognitiveLevels as $cl)
                                                <option value="{{ $cl->id }}" data-shortname="{{ $cl->shortname }}" data-fullname="{{ $cl->fullname }}">{{ $cl->shortname }} – {{ $cl->fullname }}</option>
                                                @endforeach
                                              </select>
                                            </div>
                                          </div>
                                        </div>

                                        <button type="submit" class="btn scs-btn-purple btn-sm w-100 mt-2">
                                          <i class="fas fa-plus me-1"></i>Add to Question Bank
                                        </button>
                                      </form>
                                    </div>
                                  </div>

                                  <!-- Questions List Panel -->
                                  <div class="col-12 col-lg-7 scs-qb-list-panel">
                                    <div class="scs-panel-header">
                                      <i class="fas fa-list me-2"></i>Questions in This Unit
                                    </div>
                                    <div class="scs-qb-list">
                                      @if($questions->isEmpty())
                                      <div class="scs-no-questions-alert">
                                        <div class="scs-alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                                        <div>
                                          <p class="fw-semibold mb-1">No questions added yet</p>
                                          <p class="text-muted small mb-0">Add questions using the form on the left. Questions are saved to the Question Bank for use in paper setting and moderation.</p>
                                        </div>
                                      </div>
                                      @else
                                      @foreach($questions as $qIdx => $q)
                                      <div class="scs-qb-card">
                                        <div class="d-flex align-items-start justify-content-between gap-2">
                                          <div class="scs-qb-num">{{ $qIdx + 1 }}</div>
                                          <div class="flex-grow-1">
                                            <div class="scs-qb-text">{!! Str::limit(strip_tags($q->question_text), 220) !!}</div>
                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                              <span class="scs-qb-pill pill-marks">{{ $q->marks }} Mark{{ $q->marks > 1 ? 's' : '' }}</span>
                                              <span class="scs-qb-pill pill-diff-{{ strtolower($q->difficulty) }}">{{ $q->difficulty }}</span>
                                              @if($q->cognitiveLevel)
                                              <span class="scs-qb-pill pill-bloom">{{ $q->cognitiveLevel->shortname }} – {{ $q->cognitiveLevel->fullname }}</span>
                                              @endif
                                            </div>
                                          </div>
                                          <form action="{{ route('faculty.questions.destroy', $q->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this question from the bank?');">
                                            @csrf @method('DELETE')
                                            <button class="scs-icon-btn scs-icon-danger flex-shrink-0" title="Remove"><i class="fas fa-times"></i></button>
                                          </form>
                                        </div>
                                      </div>
                                      @endforeach
                                      @endif
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        @endforeach

                        @else
                        <div class="alert alert-info mb-0">
                          <i class="fas fa-info-circle me-2"></i>
                          Syllabus Not Added Yet for this subject. Please contact your department to update the syllabus details.
                        </div>
                        @endif
                      </div>
                    </div>
                  </div>
                  @endforeach
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
      @empty
      <div class="row">
        <div class="col-12">
          <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
              <div class="mb-4">
                <i class="fas fa-book-open text-muted" style="font-size: 4rem;"></i>
              </div>
              <h5 class="text-muted">No Subjects Assigned</h5>
              <p class="text-muted mb-0">You don't have any subjects assigned yet. Please contact your department.</p>
            </div>
          </div>
        </div>
      </div>
      @endforelse
    </div>
  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')

<style>
  /* ============================================
     SCS ERP — Subjects Page Custom Styles
     ============================================ */

  /* Page header gradient */
  .bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  /* Utility */
  .bg-light-info {
    background-color: #e7f3ff;
  }

  .bg-light-warning {
    background-color: #fff8e6;
  }

  .text-info {
    color: #0dcaf0 !important;
  }

  .text-warning {
    color: #ffc107 !important;
  }

  .text-purple {
    color: #7c3aed !important;
  }

  .fw-500 {
    font-weight: 500;
  }

  /* ---------- Stat Pills (card-header summary) ---------- */
  .scs-stat-pill {
    display: inline-flex;
    align-items: center;
    font-size: .72rem;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
    white-space: nowrap;
  }

  .scs-stat-success {
    background: #d1fae5;
    color: #065f46;
  }

  .scs-stat-warning {
    background: #fef3c7;
    color: #92400e;
  }

  .scs-stat-primary {
    background: #dbeafe;
    color: #1e40af;
  }

  /* ---------- Instructional Objectives Table ---------- */
  .scs-unit-table {
    border-collapse: separate;
    border-spacing: 0;
  }

  .scs-unit-table thead tr th {
    background: #f8faff;
    color: #475569;
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    padding: 10px 12px;
    border-top: none;
    border-bottom: 2px solid #e2e8f0;
  }

  .scs-unit-table tbody tr td {
    padding: 10px 12px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
  }

  .scs-unit-row {
    transition: background .15s;
  }

  .scs-unit-row:hover td {
    background: #fafbff;
  }

  .scs-row-done td {
    background: #f0fdf4;
  }

  .scs-row-done:hover td {
    background: #dcfce7;
  }

  .scs-serial {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #475569;
    font-size: .75rem;
    font-weight: 700;
  }

  /* Unit title with dot indicator */
  .scs-unit-dot {
    flex-shrink: 0;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-top: 5px;
  }

  .dot-done {
    background: #22c55e;
    box-shadow: 0 0 0 3px #dcfce7;
  }

  .dot-pending {
    background: #f59e0b;
    box-shadow: 0 0 0 3px #fef3c7;
  }

  .scs-unit-title {
    font-size: .84rem;
    font-weight: 500;
    color: #1e293b;
    line-height: 1.4;
  }

  /* ---------- Bloom's Level Badges ---------- */
  .scs-bloom-badge {
    display: inline-block;
    font-size: .7rem;
    padding: 3px 9px;
    border-radius: 12px;
    font-weight: 600;
  }

  .scs-bloom-rm {
    background: #e0e7ff;
    color: #3730a3;
  }

  .scs-bloom-un {
    background: #d1fae5;
    color: #065f46;
  }

  .scs-bloom-ap {
    background: #fef3c7;
    color: #92400e;
  }

  .scs-bloom-an {
    background: #fee2e2;
    color: #991b1b;
  }

  .scs-bloom-ev {
    background: #ede9fe;
    color: #5b21b6;
  }

  .scs-bloom-cr {
    background: #fce7f3;
    color: #9d174d;
  }

  /* ---------- Status Badges ---------- */
  .scs-status-badge {
    display: inline-block;
    font-size: .7rem;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 12px;
  }

  .scs-status-done {
    background: #d1fae5;
    color: #065f46;
  }

  .scs-status-pending {
    background: #fef3c7;
    color: #92400e;
  }

  /* ---------- Tool Buttons (Resources / Questions) ---------- */
  .scs-tool-btn {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 8px;
    border: none;
    font-size: .9rem;
    cursor: pointer;
    transition: transform .15s, box-shadow .15s;
  }

  .scs-tool-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
  }

  .scs-tool-info {
    background: #e0f2fe;
    color: #0369a1;
  }

  .scs-tool-info:hover {
    background: #bae6fd;
  }

  .scs-tool-purple {
    background: #ede9fe;
    color: #6d28d9;
  }

  .scs-tool-purple:hover {
    background: #ddd6fe;
  }

  .scs-count-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    min-width: 18px;
    height: 18px;
    border-radius: 9px;
    font-size: .62rem;
    font-weight: 700;
    line-height: 18px;
    text-align: center;
    padding: 0 4px;
  }

  .count-active {
    background: #3b82f6;
    color: #fff;
  }

  .count-empty {
    background: #94a3b8;
    color: #fff;
  }

  /* ---------- Toggle Done Button ---------- */
  .scs-toggle-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 2px solid;
    font-size: .85rem;
    cursor: pointer;
    text-decoration: none;
    transition: all .15s;
  }

  .toggle-done {
    border-color: #22c55e;
    color: #22c55e;
    background: #f0fdf4;
  }

  .toggle-done:hover {
    background: #22c55e;
    color: #fff;
  }

  .toggle-pending {
    border-color: #f59e0b;
    color: #f59e0b;
    background: #fefce8;
  }

  .toggle-pending:hover {
    background: #f59e0b;
    color: #fff;
  }

  /* ---------- Modal Styles ---------- */
  .scs-modal {
    border-radius: 12px;
    overflow: hidden;
  }

  .scs-modal-header-teal {
    background: linear-gradient(135deg, #0891b2, #0e7490);
    color: #fff;
    padding: 16px 20px;
  }

  .scs-modal-header-purple {
    background: linear-gradient(135deg, #7c3aed, #5b21b6);
    color: #fff;
    padding: 16px 20px;
  }

  /* Upload strip */
  .scs-upload-strip {
    background: #f8faff;
    border-bottom: 1px solid #e2e8f0;
    padding: 14px 18px;
  }

  /* Resource list */
  .scs-list-body {
    max-height: 320px;
    overflow-y: auto;
  }

  .scs-resource-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 18px;
    border-bottom: 1px solid #f1f5f9;
    transition: background .12s;
  }

  .scs-resource-item:hover {
    background: #f8faff;
  }

  .scs-resource-icon {
    flex-shrink: 0;
    width: 32px;
    text-align: center;
  }

  .scs-resource-info {
    flex-grow: 1;
    min-width: 0;
  }

  .scs-resource-name {
    font-size: .84rem;
    font-weight: 500;
    color: #1e293b;
    margin-bottom: 2px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .scs-resource-meta {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    font-size: .72rem;
    color: #64748b;
  }

  .scs-resource-meta span {
    display: flex;
    align-items: center;
    gap: 4px;
  }

  .scs-resource-actions {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
  }

  /* Empty state */
  .scs-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
  }

  /* ---------- Question Bank Panel ---------- */
  .scs-qb-add-panel {
    border-right: 1px solid #e2e8f0;
    background: #f8faff;
  }

  .scs-qb-list-panel {
    background: #fff;
  }

  .scs-panel-header {
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #64748b;
    padding: 10px 16px;
    background: #f1f5f9;
    border-bottom: 1px solid #e2e8f0;
  }

  .scs-form-group {
    margin-bottom: 10px;
  }

  .scs-form-label {
    font-size: .76rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 4px;
    display: block;
  }

  .scs-qb-list {
    max-height: 480px;
    overflow-y: auto;
    padding: 8px;
  }

  .scs-qb-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 12px;
    margin-bottom: 8px;
    transition: box-shadow .15s;
  }

  .scs-qb-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
  }

  .scs-qb-num {
    flex-shrink: 0;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #ede9fe;
    color: #5b21b6;
    font-size: .7rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .scs-qb-text {
    font-size: .84rem;
    color: #1e293b;
    margin-bottom: 0;
    line-height: 1.4;
  }

  /* QBank pills */
  .scs-qb-pill {
    display: inline-block;
    font-size: .65rem;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 10px;
  }

  .pill-type {
    background: #e0e7ff;
    color: #3730a3;
  }

  .pill-marks {
    background: #d1fae5;
    color: #065f46;
  }

  .pill-bloom {
    background: #fce7f3;
    color: #9d174d;
  }

  .pill-diff-easy {
    background: #d1fae5;
    color: #065f46;
  }

  .pill-diff-medium {
    background: #fef3c7;
    color: #92400e;
  }

  .pill-diff-hard {
    background: #fee2e2;
    color: #991b1b;
  }

  /* No-questions alert */
  .scs-no-questions-alert {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    margin: 16px;
    padding: 14px 16px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 8px;
    color: #92400e;
  }

  .scs-alert-icon {
    font-size: 1.3rem;
    margin-top: 2px;
    flex-shrink: 0;
  }

  /* ---------- Action buttons (icon) ---------- */
  .scs-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: none;
    font-size: .75rem;
    cursor: pointer;
    transition: all .12s;
  }

  .scs-icon-primary {
    background: #dbeafe;
    color: #1d4ed8;
  }

  .scs-icon-primary:hover {
    background: #1d4ed8;
    color: #fff;
  }

  .scs-icon-danger {
    background: #fee2e2;
    color: #dc2626;
  }

  .scs-icon-danger:hover {
    background: #dc2626;
    color: #fff;
  }

  /* ---------- Custom buttons ---------- */
  .scs-btn-teal {
    background: #0891b2;
    color: #fff;
    border: none;
  }

  .scs-btn-teal:hover {
    background: #0e7490;
    color: #fff;
  }

  .scs-btn-purple {
    background: #7c3aed;
    color: #fff;
    border: none;
  }

  .scs-btn-purple:hover {
    background: #5b21b6;
    color: #fff;
  }

  /* ---------- Accordion tweaks ---------- */
  .accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    color: #212529;
  }

  .accordion-button:focus {
    box-shadow: none;
    border-color: rgba(0, 0, 0, .125);
  }

  .semester-section:last-child {
    border-bottom: none !important;
  }

  .semester-header {
    position: sticky;
    top: 0;
    z-index: 10;
  }

  /* ---------- Responsive ---------- */
  @media (max-width: 768px) {
    .accordion-button {
      flex-direction: column;
      align-items: flex-start !important;
    }

    .accordion-button>div {
      width: 100%;
    }

    .accordion-button .d-flex.gap-3 {
      flex-wrap: wrap;
      margin-top: 10px;
    }

    .scs-qb-add-panel {
      border-right: none;
      border-bottom: 1px solid #e2e8f0;
    }
  }

  /* ---------- CKEditor inside Bootstrap modals ---------- */
  .scs-qb-editor {
    visibility: hidden;
    height: 0;
  }

  .modal .ck.ck-editor {
    border-radius: 6px;
    overflow: hidden;
  }

  .modal .ck.ck-editor__main>.ck-editor__editable {
    min-height: 110px;
    font-size: .84rem;
  }

  .modal .ck.ck-toolbar {
    border-radius: 6px 6px 0 0 !important;
    background: #f1f5f9 !important;
    border-color: #e2e8f0 !important;
    padding: 4px 6px !important;
  }

  .modal .ck.ck-toolbar .ck.ck-button {
    min-width: 24px;
    min-height: 24px;
    padding: 3px 5px;
  }

  .ck.ck-balloon-panel {
    z-index: 99999 !important;
  }

  .ck.ck-body .ck.ck-balloon-panel {
    z-index: 99999 !important;
  }

  /* ---------- Bloom's Taxonomy Alert ---------- */
  .scs-bloom-alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    background: #fff7ed;
    border: 1px solid #fed7aa;
    border-left: 4px solid #f97316;
    border-radius: 6px;
    padding: 10px 12px;
    margin-bottom: 10px;
    position: relative;
    animation: bloomSlideIn .2s ease;
  }

  .scs-bloom-alert-icon {
    color: #f97316;
    font-size: 1rem;
    flex-shrink: 0;
    margin-top: 1px;
  }

  .scs-bloom-alert-close {
    position: absolute;
    top: 6px;
    right: 8px;
    background: none;
    border: none;
    color: #9a3412;
    font-size: 1.1rem;
    cursor: pointer;
    line-height: 1;
    padding: 0;
  }

  .scs-bloom-verbs {
    font-size: .68rem;
    color: #c2410c;
    font-style: italic;
    margin-top: 4px;
  }

  .x-small {
    font-size: .72rem;
  }

  /* Bloom OK (green) */
  .scs-bloom-ok {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-left: 4px solid #22c55e;
    border-radius: 6px;
    padding: 8px 12px;
    margin-bottom: 10px;
    font-size: .78rem;
    font-weight: 600;
    color: #15803d;
    animation: bloomSlideIn .2s ease;
  }

  @keyframes bloomSlideIn {
    from {
      opacity: 0;
      transform: translateY(-6px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
</style>

<script>
  (function() {
    'use strict';

    // ============================================================
    // CKEditor instances keyed by unit id
    // ============================================================
    const qbEditors = new Map();

    const CK_CONFIG = {
      removePlugins: [
        'CKFinderUploadAdapter', 'CKFinder', 'EasyImage',
        'Image', 'ImageCaption', 'ImageStyle', 'ImageToolbar', 'ImageUpload', 'MediaEmbed'
      ],
      toolbar: ['bold', 'italic', 'underline', '|', 'numberedList', 'bulletedList', '|', 'undo', 'redo']
    };

    // ============================================================
    // Wire up each Question modal
    // ============================================================
    document.addEventListener('DOMContentLoaded', function() {

      // Smooth-scroll accordions
      document.querySelectorAll('.accordion-button').forEach(function(btn) {
        btn.addEventListener('click', function() {
          setTimeout(() => {
            if (!this.classList.contains('collapsed')) {
              this.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
              });
            }
          }, 350);
        });
      });

      // Question modals
      document.querySelectorAll('[id^="questionModal"]').forEach(function(modal) {
        const unitId = modal.id.replace('questionModal', '');
        const textareaId = 'questionText' + unitId;
        const levelSelId = 'bloomLevel' + unitId;

        // Init CKEditor when modal opens
        modal.addEventListener('show.bs.modal', function() {
          const textarea = document.getElementById(textareaId);
          if (!textarea || qbEditors.has(unitId)) return;

          ClassicEditor.create(textarea, CK_CONFIG)
            .then(function(editor) {
              qbEditors.set(unitId, editor);
            })
            .catch(function(err) {
              console.error('CKEditor init error:', err);
            });

        });

        // Destroy CKEditor when modal closes
        modal.addEventListener('hide.bs.modal', function() {
          const editor = qbEditors.get(unitId);
          if (editor) {
            editor.destroy().catch(function(e) {
              console.error(e);
            });
            qbEditors.delete(unitId);
          }
        });

        // Sync CKEditor content to textarea before form submission
        const form = modal.querySelector('form.scs-qb-form');
        if (form) {
          form.addEventListener('submit', function(e) {
            const editor = qbEditors.get(unitId);
            const textarea = document.getElementById(textareaId);
            if (editor && textarea) {
              const data = editor.getData();
              if (!data || data.replace(/<[^>]+>/g, '').trim() === '') {
                e.preventDefault();
                alert('Please enter a question before submitting.');
                return;
              }
              textarea.value = data;
            }
          });
        }
      });
    });

  }());
</script>