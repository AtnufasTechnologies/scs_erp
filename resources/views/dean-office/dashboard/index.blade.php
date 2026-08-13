@include('includes.header')

<div class=" wrapper">
  @include('dean-office.sidebar')

  <main class="page-content">
    <div class="container-fluid py-3">
      <h3 class="fw-bold mb-3">Dean Dashboard</h3>

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

      @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
      @endif

      <div class="card shadow-sm mb-3">
        <div class="card-header fw-semibold">Faculty Profile</div>
        <div class="card-body table-responsive">
          <table class="table table-bordered mb-0">
            <tr>
              <th width="20%">Faculty Name</th>
              <td width="30%">{{ $faculty->full_name ?? 'N/A' }}</td>
              <th width="20%">Employee ID</th>
              <td width="30%">{{ $faculty->USER_CODE ?? 'N/A' }}</td>
            </tr>
            <tr>
              <th>Deanery</th>
              <td>{{ $faculty->responsibility ?? 'N/A' }}</td>
              <th>Designation</th>
              <td>{{ $faculty->designation ?? ($faculty->hrDesignation->title ?? 'N/A') }}</td>
            </tr>
          </table>
        </div>
      </div>

      <ul class="nav nav-tabs" id="deanDashboardTabs" role="tablist">
        <li class="nav-item" role="presentation"><button class="nav-link active" id="tab-annual-plan" data-bs-toggle="tab" data-bs-target="#pane-annual-plan" type="button" role="tab">1. Annual Plan</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-weekly" data-bs-toggle="tab" data-bs-target="#pane-weekly" type="button" role="tab">2. Weekly Progress</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-lesson" data-bs-toggle="tab" data-bs-target="#pane-lesson" type="button" role="tab">3. Lesson Plan</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-api" data-bs-toggle="tab" data-bs-target="#pane-api" type="button" role="tab">4. API Scorecard</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-todo" data-bs-toggle="tab" data-bs-target="#pane-todo" type="button" role="tab">5. Things To Do</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-comp" data-bs-toggle="tab" data-bs-target="#pane-comp" type="button" role="tab">6. HoD360 Follow-up</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-score" data-bs-toggle="tab" data-bs-target="#pane-score" type="button" role="tab">7. Consolidated Score</button></li>
      </ul>

      <div class="tab-content border border-top-0 p-3 bg-white" id="deanDashboardTabsContent">
        <div class="tab-pane fade show active" id="pane-annual-plan" role="tabpanel">
          <p class="text-muted">Record academic, research, administrative, co-curricular, and professional goals for the year.</p>
          <form method="POST" action="{{ route('dean.office.annual-plan.store') }}" class="row g-2 mb-3">
            @csrf
            <div class="col-md-3"><input class="form-control" name="activity_goal" placeholder="Activity / Goal" required></div>
            <div class="col-md-2">
              <select class="form-select" name="category" required>
                <option value="">Select Category</option>
                <option value="academic" {{ old('category') === 'academic' ? 'selected' : '' }}>Academic</option>
                <option value="research" {{ old('category') === 'research' ? 'selected' : '' }}>Research</option>
                <option value="administration" {{ old('category') === 'administration' ? 'selected' : '' }}>Administrative</option>
                <option value="co-curricular" {{ old('category') === 'co-curricular' ? 'selected' : '' }}>Co-curricular</option>
                <option value="professional" {{ old('category') === 'professional' ? 'selected' : '' }}>Professional</option>
              </select>
            </div>
            <div class="col-md-2"><input class="form-control" name="target" placeholder="Target"></div>
            <div class="col-md-2"><input class="form-control" type="date" name="expected_completion_date"></div>
            <div class="col-md-1">
              <select class="form-select" name="priority">
                <option value="">Priority</option>
                <option>High</option>
                <option>Medium</option>
                <option>Low</option>
              </select>
            </div>
            <div class="col-md-2"><input class="form-control" name="semester_month" placeholder="Semester / Month"></div>
            <div class="col-md-3"><input class="form-control" name="expected_outcome" placeholder="Expected Outcome"></div>
            <div class="col-md-3"><input class="form-control" name="achievement_actual_result" placeholder="Achievement / Actual Result"></div>
            <div class="col-md-2"><input class="form-control" name="evidence_required" placeholder="Evidence Required"></div>
            <div class="col-md-2"><input class="form-control" name="status" placeholder="Status"></div>
            <div class="col-md-1"><input class="form-control" name="verified_by" placeholder="Verified"></div>
            <div class="col-md-1 d-grid"><button class="btn btn-primary">Add</button></div>
          </form>

          <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
              <thead class="table-light">
                <tr>
                  <th>Sl</th>
                  <th>Activity / Goal</th>
                  <th>Category</th>
                  <th>Target</th>
                  <th>Expected Completion</th>
                  <th>Priority</th>
                  <th>Semester / Month</th>
                  <th>Expected Outcome</th>
                  <th>Achievement / Actual Result</th>
                  <th>Evidence Required</th>
                  <th>Status</th>
                  <th>Verified</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($annualPlans as $row)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $row->activity_goal }}</td>
                  <td>{{ $row->category }}</td>
                  <td>{{ $row->target }}</td>
                  <td>{{ $row->expected_completion_date }}</td>
                  <td>{{ $row->priority }}</td>
                  <td>{{ $row->semester_month }}</td>
                  <td>{{ $row->expected_outcome }}</td>
                  <td>{{ $row->achievement_actual_result }}</td>
                  <td>{{ $row->evidence_required }}</td>
                  <td>{{ $row->status }}</td>
                  <td>{{ $row->verified_by }}</td>
                  <td>
                    <form method="POST" action="{{ route('dean.office.annual-plan.delete', $row->id) }}" onsubmit="return confirm('Delete row?')">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="13" class="text-center text-muted">No annual plan rows yet.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        <div class="tab-pane fade" id="pane-weekly" role="tabpanel">
          <div class="row mb-3">
            <div class="col-md-4">
              <div class="alert alert-info mb-0">Academic Completion: <strong>{{ number_format($completionByCategory['academic'], 2) }}%</strong></div>
            </div>
            <div class="col-md-4">
              <div class="alert alert-info mb-0">Research Completion: <strong>{{ number_format($completionByCategory['research'], 2) }}%</strong></div>
            </div>
            <div class="col-md-4">
              <div class="alert alert-info mb-0">Administration Completion: <strong>{{ number_format($completionByCategory['administration'], 2) }}%</strong></div>
            </div>
          </div>

          <form method="POST" action="{{ route('dean.office.weekly-progress.store') }}" class="row g-2 mb-3">
            @csrf
            <div class="col-md-2"><input type="date" class="form-control" name="week_date"></div>
            <div class="col-md-2"><input class="form-control" name="activities_completed" placeholder="Activities Completed"></div>
            <div class="col-md-2"><input class="form-control" name="activities_in_progress" placeholder="Activities in Progress"></div>
            <div class="col-md-2"><input class="form-control" name="pending_activities" placeholder="Pending Activities"></div>
            <div class="col-md-1"><input class="form-control" type="number" step="0.01" min="0" max="100" name="completion_percent" placeholder="%"></div>
            <div class="col-md-2"><input class="form-control" name="reason_for_delay" placeholder="Reason for Delay"></div>
            <div class="col-md-1"><input class="form-control" name="evidence_remarks" placeholder="Remarks"></div>
            <div class="col-md-12 d-grid"><button class="btn btn-primary">Add Weekly Row</button></div>
          </form>

          <div class="table-responsive">
            <table class="table table-bordered table-sm">
              <thead class="table-light">
                <tr>
                  <th>Week / Date</th>
                  <th>Activities Completed</th>
                  <th>Activities in Progress</th>
                  <th>Pending Activities</th>
                  <th>% of Completion</th>
                  <th>Reason for Delay</th>
                  <th>Evidence / Remarks</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($weeklyProgress as $row)
                <tr>
                  <td>{{ $row->week_date }}</td>
                  <td>{{ $row->activities_completed }}</td>
                  <td>{{ $row->activities_in_progress }}</td>
                  <td>{{ $row->pending_activities }}</td>
                  <td>{{ $row->completion_percent }}%</td>
                  <td>{{ $row->reason_for_delay }}</td>
                  <td>{{ $row->evidence_remarks }}</td>
                  <td>
                    <form method="POST" action="{{ route('dean.office.weekly-progress.delete', $row->id) }}" onsubmit="return confirm('Delete row?')">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="8" class="text-center text-muted">No weekly progress rows yet.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        <div class="tab-pane fade" id="pane-lesson" role="tabpanel">
          <p class="text-muted">Monitors teaching activity and syllabus progress against the academic plan.</p>
          <form method="POST" action="{{ route('dean.office.lesson-tracker.store') }}" class="row g-2 mb-3">
            @csrf
            <div class="col-md-2"><input class="form-control" name="course_subject" placeholder="Course / Subject" required></div>
            <div class="col-md-2"><input class="form-control" name="unit_module" placeholder="Unit / Module"></div>
            <div class="col-md-2"><input class="form-control" name="topics_planned" placeholder="Topics Planned"></div>
            <div class="col-md-2"><input class="form-control" type="date" name="plan_to_complete_date"></div>
            <div class="col-md-2"><input class="form-control" name="topics_completed" placeholder="Topics Completed"></div>
            <div class="col-md-2"><input class="form-control" type="date" name="completion_date"></div>
            <div class="col-md-2"><input class="form-control" type="number" min="0" name="classes_planned" placeholder="Classes Planned"></div>
            <div class="col-md-2"><input class="form-control" name="assessment_conducted" placeholder="Assessment Conducted"></div>
            <div class="col-md-2"><input class="form-control" type="number" step="0.01" min="0" max="100" name="syllabus_completion_percent" placeholder="Syllabus %"></div>
            <div class="col-md-2 d-grid"><button class="btn btn-primary">Add Lesson Row</button></div>
          </form>

          <div class="table-responsive">
            <table class="table table-bordered table-sm">
              <thead class="table-light">
                <tr>
                  <th>Course / Subject</th>
                  <th>Unit / Module</th>
                  <th>Topics Planned</th>
                  <th>Plan Date</th>
                  <th>Topics Completed</th>
                  <th>Completed Date</th>
                  <th>Classes Planned</th>
                  <th>Assessment Conducted</th>
                  <th>Syllabus %</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($lessonTrackers as $row)
                <tr>
                  <td>{{ $row->course_subject }}</td>
                  <td>{{ $row->unit_module }}</td>
                  <td>{{ $row->topics_planned }}</td>
                  <td>{{ $row->plan_to_complete_date }}</td>
                  <td>{{ $row->topics_completed }}</td>
                  <td>{{ $row->completion_date }}</td>
                  <td>{{ $row->classes_planned }}</td>
                  <td>{{ $row->assessment_conducted }}</td>
                  <td>{{ $row->syllabus_completion_percent }}%</td>
                  <td>
                    <form method="POST" action="{{ route('dean.office.lesson-tracker.delete', $row->id) }}" onsubmit="return confirm('Delete row?')">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="10" class="text-center text-muted">No lesson tracker rows yet.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        <div class="tab-pane fade" id="pane-api" role="tabpanel">
          <form method="POST" action="{{ route('dean.office.scorecard.store') }}" class="row g-2 mb-3">
            @csrf
            <div class="col-md-2"><input class="form-control" name="category" placeholder="Category" required></div>
            <div class="col-md-3"><input class="form-control" name="covers" placeholder="Covers"></div>
            <div class="col-md-1"><input class="form-control" name="max_score" type="number" step="0.01" min="0" placeholder="Score"></div>
            <div class="col-md-1"><input class="form-control" name="score_given" type="number" step="0.01" min="0" placeholder="Given"></div>
            <div class="col-md-2"><input class="form-control" name="verified_by" placeholder="Verified By"></div>
            <div class="col-md-2"><input class="form-control" name="reviewed_by" placeholder="Reviewed By"></div>
            <div class="col-md-1 d-grid"><button class="btn btn-primary">Add</button></div>
            <div class="col-md-12"><input class="form-control" name="remarks" placeholder="Remarks"></div>
          </form>

          <div class="table-responsive mb-3">
            <table class="table table-bordered table-sm">
              <thead class="table-light">
                <tr>
                  <th>Category</th>
                  <th>Covers</th>
                  <th>Score</th>
                  <th>Score Given</th>
                  <th>Verified By</th>
                  <th>Reviewed By</th>
                  <th>Remarks</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($scorecards as $row)
                <tr>
                  <td>{{ $row->category }}</td>
                  <td>{{ $row->covers }}</td>
                  <td>{{ $row->max_score }}</td>
                  <td>{{ $row->score_given }}</td>
                  <td>{{ $row->verified_by }}</td>
                  <td>{{ $row->reviewed_by }}</td>
                  <td>{{ $row->remarks }}</td>
                  <td>
                    <form method="POST" action="{{ route('dean.office.scorecard.delete', $row->id) }}" onsubmit="return confirm('Delete row?')">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="8" class="text-center text-muted">No scorecard rows yet.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="row g-2">
            <div class="col-md-3">
              <div class="alert alert-secondary mb-0">Total Max Score: <strong>{{ $scoreSummary['total_max'] }}</strong></div>
            </div>
            <div class="col-md-3">
              <div class="alert alert-secondary mb-0">Overall Score Given: <strong>{{ $scoreSummary['total_given'] }}</strong></div>
            </div>
            <div class="col-md-3">
              <div class="alert alert-secondary mb-0">Administrative Score: <strong>{{ $scoreSummary['admin_given'] }}</strong></div>
            </div>
            <div class="col-md-3">
              <div class="alert alert-secondary mb-0">Academic Score: <strong>{{ $scoreSummary['academic_given'] }}</strong></div>
            </div>
          </div>
        </div>

        <div class="tab-pane fade" id="pane-todo" role="tabpanel">
          <form method="POST" action="{{ route('dean.office.tasks.store') }}" class="row g-2 mb-3">
            @csrf
            <div class="col-md-3"><input class="form-control" name="task" placeholder="Task" required></div>
            <div class="col-md-2"><input class="form-control" name="category" placeholder="Category"></div>
            <div class="col-md-2"><input type="date" class="form-control" name="due_date"></div>
            <div class="col-md-1"><input class="form-control" name="priority" placeholder="Priority"></div>
            <div class="col-md-2"><input class="form-control" name="assigned_by" placeholder="Assigned By"></div>
            <div class="col-md-1"><input class="form-control" name="status" placeholder="Status"></div>
            <div class="col-md-1 d-grid"><button class="btn btn-primary">Add</button></div>
            <div class="col-md-12"><input class="form-control" name="evidence_remarks" placeholder="Evidence / Remarks"></div>
          </form>

          <div class="table-responsive">
            <table class="table table-bordered table-sm">
              <thead class="table-light">
                <tr>
                  <th>Task</th>
                  <th>Category</th>
                  <th>Due Date</th>
                  <th>Priority</th>
                  <th>Assigned By</th>
                  <th>Status</th>
                  <th>Evidence / Remarks</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($tasks as $row)
                <tr>
                  <td>{{ $row->task }}</td>
                  <td>{{ $row->category }}</td>
                  <td>{{ $row->due_date }}</td>
                  <td>{{ $row->priority }}</td>
                  <td>{{ $row->assigned_by }}</td>
                  <td>{{ $row->status }}</td>
                  <td>{{ $row->evidence_remarks }}</td>
                  <td>
                    <form method="POST" action="{{ route('dean.office.tasks.delete', $row->id) }}" onsubmit="return confirm('Delete task?')">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="8" class="text-center text-muted">No tasks added yet.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        <div class="tab-pane fade" id="pane-comp" role="tabpanel">
          <p class="fw-semibold">HoD360 Academic Monitoring and Follow-up</p>
          <p class="text-muted mb-3">Track subject coverage, program combinations, curricula status, and teaching assignment readiness with remarks and follow-up state.</p>

          <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
              <thead class="table-light">
                <tr>
                  <th width="4%">Sl</th>
                  <th width="40%">Academic Metric</th>
                  <th width="10%">Current Value</th>
                  <th width="28%">Remarks / Follow-up Notes</th>
                  <th width="10%">Status</th>
                  <th width="8%">Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($hod360Rows as $row)
                @php
                $saved = $hod360Followups->get($row['metric_code']);
                @endphp
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $row['title'] }}</td>
                  <td><strong>{{ $row['value'] }}</strong></td>
                  <td colspan="3">
                    <form method="POST" action="{{ route('dean.office.hod360.followup.upsert') }}" class="row g-2 align-items-center">
                      @csrf
                      <input type="hidden" name="metric_code" value="{{ $row['metric_code'] }}">
                      <input type="hidden" name="title" value="{{ $row['title'] }}">
                      <div class="col-md-7">
                        <input class="form-control form-control-sm" name="remarks" value="{{ $saved->remarks ?? '' }}" placeholder="Actionable follow-up remarks">
                      </div>
                      <div class="col-md-3">
                        <select class="form-select form-select-sm" name="status">
                          <option value="open" {{ ($saved->status ?? 'open') === 'open' ? 'selected' : '' }}>Open</option>
                          <option value="in-progress" {{ ($saved->status ?? '') === 'in-progress' ? 'selected' : '' }}>In Progress</option>
                          <option value="resolved" {{ ($saved->status ?? '') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        </select>
                      </div>
                      <div class="col-md-2 d-grid">
                        <button class="btn btn-sm btn-primary">Save</button>
                      </div>
                    </form>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="6" class="text-center text-muted">No HoD360 academic metrics available.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="alert alert-light border mt-2 mb-0">
            <strong>Note:</strong>
            <span class="text-muted">This section is intended for dean-level academic review and follow-up with departments on offered combinations, curriculum coverage, and teaching readiness.</span>
          </div>
        </div>

        <div class="tab-pane fade" id="pane-score" role="tabpanel">
          <div class="table-responsive">
            <table class="table table-bordered align-middle">
              <thead class="table-dark">
                <tr>
                  <th>Category</th>
                  <th width="120">Weight</th>
                  <th width="180">Score Obtained</th>
                  <th>Remarks</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>D. Administrative & Academic Management</td>
                  <td>35</td>
                  <td>{{ $scoreSummary['admin_given'] }}</td>
                  <td></td>
                </tr>
                <tr>
                  <td>E. Professional Development</td>
                  <td>10</td>
                  <td></td>
                  <td></td>
                </tr>
                <tr>
                  <td>G. Discipline, Governance & Leadership</td>
                  <td>5</td>
                  <td></td>
                  <td></td>
                </tr>
                <tr class="table-primary">
                  <td><strong>Total I (API_ Administrative)</strong></td>
                  <td><strong>50</strong></td>
                  <td><strong>{{ $scoreSummary['admin_given'] }}</strong></td>
                  <td></td>
                </tr>

                <tr>
                  <td>A. Teaching output</td>
                  <td>10</td>
                  <td>{{ $scoreSummary['academic_given'] }}</td>
                  <td></td>
                </tr>
                <tr>
                  <td>B. Learning & Evaluation</td>
                  <td>25</td>
                  <td></td>
                  <td></td>
                </tr>
                <tr>
                  <td>C. Co-curricular Activities (Department)</td>
                  <td>5</td>
                  <td></td>
                  <td></td>
                </tr>
                <tr>
                  <td>F. Research, Innovation & Quality</td>
                  <td>10</td>
                  <td></td>
                  <td></td>
                </tr>
                <tr class="table-primary">
                  <td><strong>Total II (API_ Academic)</strong></td>
                  <td><strong>50</strong></td>
                  <td><strong>{{ $scoreSummary['academic_given'] }}</strong></td>
                  <td></td>
                </tr>

                <tr class="table-secondary">
                  <td><strong>Grand Total I+II (Leadership of Dean)</strong></td>
                  <td><strong>100</strong></td>
                  <td><strong>{{ $scoreSummary['total_given'] }}</strong></td>
                  <td></td>
                </tr>
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')