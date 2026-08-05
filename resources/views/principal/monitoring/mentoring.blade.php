@include('includes.header')
<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="container-fluid py-3">
      <div class="card border-0 shadow-lg mb-4" style="background: linear-gradient(120deg, #0f766e 0%, #1d4ed8 100%);">
        <div class="card-body text-white py-4">
          <h3 class="mb-1">Mentoring Monitor</h3>
          <p class="mb-0 opacity-75">Principal view-only panel for mentoring sessions, enrollments, and attendance trends.</p>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body"><small class="text-muted">Groups</small>
              <h4 class="mb-0">{{ $summary['total_groups'] }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body"><small class="text-muted">Sessions</small>
              <h4 class="mb-0">{{ $summary['total_sessions'] }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body"><small class="text-muted">High-risk Students</small>
              <h4 class="mb-0 text-danger">{{ $summary['high_risk_students'] }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body"><small class="text-muted">Attendance %</small>
              <h4 class="mb-0">{{ $summary['session_attendance_pct'] }}%</h4>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white">Latest Sessions</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered align-middle">
            <thead>
              <tr>
                <th>Date</th>
                <th>Session</th>
                <th>Group</th>
                <th>Faculty</th>
                <th>Attendance</th>
                <th>Present</th>
                <th>%</th>
              </tr>
            </thead>
            <tbody>
              @forelse($sessions as $session)
              @php
              $total = (int) ($session->attendances_count ?? 0);
              $present = (int) ($session->present_count ?? 0);
              $pct = $total > 0 ? round(($present / $total) * 100, 2) : 0;
              @endphp
              <tr>
                <td>{{ optional($session->session_date)->format('d-M-Y') }}</td>
                <td>{{ $session->title }}</td>
                <td>{{ $session->group->name ?? '-' }}</td>
                <td>{{ trim(($session->group->faculty->FIRST_NAME ?? '') . ' ' . ($session->group->faculty->LAST_NAME ?? '')) ?: '-' }}</td>
                <td>{{ $total }}</td>
                <td>{{ $present }}</td>
                <td>{{ $pct }}%</td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted">No sessions found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
          {{ $sessions->links() }}
        </div>
      </div>

      <div class="row g-3">
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">Mentoring Groups</div>
            <div class="card-body table-responsive">
              <table class="table table-sm table-bordered mb-0">
                <thead>
                  <tr>
                    <th>Group</th>
                    <th>Mentor</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($groups as $group)
                  <tr>
                    <td>{{ $group->name }}</td>
                    <td>{{ trim(($group->faculty->FIRST_NAME ?? '') . ' ' . ($group->faculty->LAST_NAME ?? '')) ?: '-' }}</td>
                    <td>{{ ucfirst((string) ($group->status ?? '-')) }}</td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="3" class="text-center text-muted">No groups found.</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
              {{ $groups->links() }}
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">High-risk / Counselling Notes</div>
            <div class="card-body table-responsive">
              <table class="table table-sm table-bordered mb-0">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Student</th>
                    <th>Category</th>
                    <th>Note</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($highRiskNotes as $note)
                  <tr>
                    <td>{{ optional($note->created_at)->format('d-M-Y') }}</td>
                    <td>{{ ($note->student->first_name ?? '') . ' ' . ($note->student->last_name ?? '') }}</td>
                    <td>{{ $note->category }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($note->note, 80) }}</td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="4" class="text-center text-muted">No high-risk notes found.</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm mt-3">
        <div class="card-header bg-white">Assignments and Enrollments</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Assignment</th>
                <th>Group</th>
                <th>Due Date</th>
                <th>Submissions</th>
                <th>Enrolled Student</th>
                <th>Roll No</th>
              </tr>
            </thead>
            <tbody>
              @forelse($assignments as $assignment)
              @php $enrollment = $enrollments->firstWhere('group_id', $assignment->group_id); @endphp
              <tr>
                <td>{{ $assignment->title }}</td>
                <td>{{ $assignment->group->name ?? '-' }}</td>
                <td>{{ optional($assignment->due_date)->format('d-M-Y') ?: '-' }}</td>
                <td>{{ (int) ($assignment->submissions_count ?? 0) }}</td>
                <td>{{ $enrollment ? (($enrollment->student->first_name ?? '') . ' ' . ($enrollment->student->last_name ?? '')) : '-' }}</td>
                <td>{{ $enrollment->student->roll_no ?? '-' }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center text-muted">No assignments found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
          {{ $assignments->links() }}
        </div>
      </div>

      <div class="card border-0 shadow-sm mt-3">
        <div class="card-header bg-white">Recent Attendance Records</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered mb-0">
            <thead>
              <tr>
                <th>Date</th>
                <th>Session</th>
                <th>Group</th>
                <th>Student</th>
                <th>Roll No</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($attendanceRecords as $record)
              <tr>
                <td>{{ optional($record->session->session_date ?? null)->format('d-M-Y') ?: '-' }}</td>
                <td>{{ $record->session->title ?? '-' }}</td>
                <td>{{ $record->session->group->name ?? '-' }}</td>
                <td>{{ ($record->student->first_name ?? '') . ' ' . ($record->student->last_name ?? '') }}</td>
                <td>{{ $record->student->roll_no ?? '-' }}</td>
                <td>{{ ucfirst((string) ($record->status ?? '-')) }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center text-muted">No attendance records found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
          {{ $attendanceRecords->links() }}
        </div>
      </div>
    </div>
  </main>
</div>
@include('includes.footer')