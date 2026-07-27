@include('includes.header')
<div class="wrapper">
  @include('student-affairs.sidebar')
  <main class="page-content">
    <div class="container-fluid py-3">
      <h3>Student 360 Profile</h3>

      <div class="card mb-3 shadow-sm">
        <div class="card-body">
          <form method="GET" action="{{ route('dean.student360.index') }}" class="row g-2">
            <div class="col-md-5">
              <select name="student_id" class="dselect-example" required>
                <option value="">Select student</option>
                @foreach($students as $student)
                <option value="{{ $student->id }}" {{ (int) request('student_id') === (int) $student->id ? 'selected' : '' }}>
                  {{ $student->roll_no }} - {{ $student->first_name }} {{ $student->last_name }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Load</button></div>
          </form>
        </div>
      </div>

      @if($profile)
      <div class="card shadow-sm mb-3">
        <div class="card-header">Student Snapshot</div>
        <div class="card-body">
          <div class="row g-2">
            <div class="col-md-3"><strong>Name:</strong> {{ ($profile['student']->first_name ?? '') . ' ' . ($profile['student']->last_name ?? '') }}</div>
            <div class="col-md-3"><strong>Roll No:</strong> {{ $profile['student']->roll_no ?? '-' }}</div>
            <div class="col-md-3"><strong>Register No:</strong> {{ $profile['student']->register_no ?? '-' }}</div>
            <div class="col-md-3"><strong>Department:</strong> {{ $profile['student']->subjectmaster->title ?? '-' }}</div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <div class="card">
            <div class="card-body"><small>Attendance</small>
              <h4>{{ $profile['attendance_pct'] }}%</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card">
            <div class="card-body"><small>Mentoring Notes</small>
              <h4>{{ $profile['mentoring_notes']->count() }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card">
            <div class="card-body"><small>Discipline Cases</small>
              <h4>{{ $profile['discipline_cases']->count() }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card">
            <div class="card-body"><small>Counselling Cases</small>
              <h4>{{ $profile['counselling_cases']->count() }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card">
            <div class="card-body"><small>Mentorship Attendance</small>
              <h4>{{ $profile['mentorship_attendance_pct'] }}%</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card">
            <div class="card-body"><small>Assignment Submissions</small>
              <h4>{{ $profile['assignment_submission_count'] }}</h4>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header">Club & Council Involvement</div>
            <div class="card-body table-responsive">
              <table class="table table-sm table-bordered mb-2">
                <thead>
                  <tr>
                    <th colspan="4">Club Memberships</th>
                  </tr>
                  <tr>
                    <th>Club</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($profile['club_memberships'] as $clubMember)
                  <tr>
                    <td>{{ $clubMember->club->name ?? '-' }}</td>
                    <td>{{ $clubMember->role_title ?? '-' }}</td>
                    <td>{{ $clubMember->status ?? '-' }}</td>
                    <td>{{ optional($clubMember->joined_on)->format('d-M-Y') ?: '-' }}</td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="4" class="text-center text-muted">No club memberships</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>

              <table class="table table-sm table-bordered mb-0">
                <thead>
                  <tr>
                    <th colspan="4">Student Council Roles</th>
                  </tr>
                  <tr>
                    <th>Council</th>
                    <th>Role</th>
                    <th>Executive</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($profile['council_roles'] as $role)
                  <tr>
                    <td>{{ $role->council->title ?? '-' }}</td>
                    <td>{{ $role->role_title ?? '-' }}</td>
                    <td>{{ $role->is_executive ? 'Yes' : 'No' }}</td>
                    <td>{{ $role->status ?? '-' }}</td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="4" class="text-center text-muted">No council roles</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header">Mentoring & Assignment Insight</div>
            <div class="card-body">
              <p class="mb-2"><strong>Academic Attendance:</strong> {{ $profile['attendance_present'] }}/{{ $profile['attendance_total'] }} present ({{ $profile['attendance_pct'] }}%)</p>
              <p class="mb-2"><strong>Mentorship Attendance:</strong> {{ $profile['mentorship_attendance_present'] }}/{{ $profile['mentorship_attendance_total'] }} present ({{ $profile['mentorship_attendance_pct'] }}%)</p>
              <p class="mb-3"><strong>Assignments Graded:</strong> {{ $profile['assignment_graded_count'] }} of {{ $profile['assignment_submission_count'] }} submissions</p>

              <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Category</th>
                      <th>Note</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($profile['mentoring_notes'] as $note)
                    <tr>
                      <td>{{ optional($note->created_at)->format('d-M-Y') }}</td>
                      <td>{{ $note->category ?? '-' }}</td>
                      <td>{{ \Illuminate\Support\Str::limit($note->note, 100) }}</td>
                    </tr>
                    @empty
                    <tr>
                      <td colspan="3" class="text-center text-muted">No mentoring notes</td>
                    </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm mb-3">
        <div class="card-header">Discipline Cases with Actions Taken</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Case No</th>
                <th>Status</th>
                <th>Severity</th>
                <th>Incident</th>
                <th>Summary</th>
                <th>Actions</th>
                <th>Last Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($profile['discipline_cases'] as $case)
              <tr>
                <td>{{ $case->case_no }}</td>
                <td>
                  @if(in_array($case->status, ['resolved', 'closed']))
                  <span class="badge bg-success">{{ strtoupper($case->status) }}</span>
                  @elseif($case->status === 'in_progress')
                  <span class="badge bg-warning text-dark">IN PROGRESS</span>
                  @else
                  <span class="badge bg-danger">{{ strtoupper($case->status ?? 'OPEN') }}</span>
                  @endif
                </td>
                <td>{{ strtoupper($case->severity ?? '-') }}</td>
                <td>{{ optional($case->incident_date)->format('d-M-Y') ?: '-' }}</td>
                <td>{{ \Illuminate\Support\Str::limit($case->summary, 80) }}</td>
                <td>{{ $case->actions->count() }}</td>
                <td>{{ strtoupper($case->actions->first()->action_type ?? '-') }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted">No discipline cases</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="card shadow-sm mb-3">
        <div class="card-header">Counselling Cases & Followups</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Case No</th>
                <th>Status</th>
                <th>Risk</th>
                <th>Concern</th>
                <th>Referred On</th>
                <th>Followups</th>
                <th>Latest Followup</th>
              </tr>
            </thead>
            <tbody>
              @forelse($profile['counselling_cases'] as $case)
              <tr>
                <td>{{ $case->case_no }}</td>
                <td>{{ strtoupper($case->status ?? '-') }}</td>
                <td>{{ strtoupper($case->risk_level ?? '-') }}</td>
                <td>{{ $case->concern_category ?? '-' }}</td>
                <td>{{ optional($case->referred_on)->format('d-M-Y') ?: '-' }}</td>
                <td>{{ $case->followups->count() }}</td>
                <td>{{ optional($case->followups->first()->followup_date ?? null)->format('d-M-Y') ?: '-' }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted">No counselling cases</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="card shadow-sm mb-3">
        <div class="card-header">Attendance Regularization History</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Date</th>
                <th>Original</th>
                <th>Effective</th>
                <th>Request</th>
              </tr>
            </thead>
            <tbody>
              @forelse($profile['attendance_regularizations'] as $r)
              <tr>
                <td>{{ optional($r->attendance_date)->format('d-M-Y') }}</td>
                <td>{{ $r->original_status }}</td>
                <td>{{ $r->effective_status }}</td>
                <td>{{ $r->regularization->request_no ?? '-' }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="text-center text-muted">No regularization history</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      @endif
    </div>
  </main>
</div>
@include('includes.footer')