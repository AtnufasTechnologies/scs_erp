@include('includes.header')
<div class="wrapper">
  @include('student-affairs.sidebar')
  <main class="page-content">
    <div class="container-fluid py-3">
      <h3>Mentoring Dashboard (Read-only)</h3>

      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body"><small>Groups</small>
              <h4>{{ $summary['total_groups'] }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body"><small>Sessions</small>
              <h4>{{ $summary['total_sessions'] }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body"><small>High-risk students</small>
              <h4 class="text-danger">{{ $summary['high_risk_students'] }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body"><small>Session attendance</small>
              <h4>{{ $summary['session_attendance_pct'] }}%</h4>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm mb-3">
        <div class="card-header">Mentor-wise Groups</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Group</th>
                <th>Mentor</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($groups as $group)
              <tr>
                <td>{{ $group->name }}</td>
                <td>{{ trim(($group->faculty->FIRST_NAME ?? '').' '.($group->faculty->LAST_NAME ?? '')) ?: '-' }}</td>
                <td>{{ $group->status }}</td>
                <td>
                  <button type="button"
                    class="btn btn-sm btn-outline-primary js-open-mentor-modal"
                    data-bs-toggle="modal"
                    data-bs-target="#mentorDetailsModal"
                    data-title="Group Details"
                    data-content="{{ e('Group: ' . ($group->name ?? '-') . "\n" . 'Mentor: ' . (trim(($group->faculty->FIRST_NAME ?? '') . ' ' . ($group->faculty->LAST_NAME ?? '')) ?: '-') . "\n" . 'Status: ' . ($group->status ?? '-')) }}">
                    View
                  </button>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="text-center text-muted">No groups found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
          {{ $groups->links() }}
        </div>
      </div>

      <div class="card shadow-sm mb-3">
        <div class="card-header">Faculty Session Details with Attendance</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Date</th>
                <th>Session</th>
                <th>Group</th>
                <th>Faculty</th>
                <th>Attendance</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Excused</th>
                <th>%</th>
                <th>Action</th>
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
                <td>{{ trim(($session->group->faculty->FIRST_NAME ?? '').' '.($session->group->faculty->LAST_NAME ?? '')) ?: '-' }}</td>
                <td>{{ $total }}</td>
                <td>{{ $present }}</td>
                <td>{{ (int) ($session->absent_count ?? 0) }}</td>
                <td>{{ (int) ($session->excused_count ?? 0) }}</td>
                <td>{{ $pct }}%</td>
                <td>
                  <button type="button"
                    class="btn btn-sm btn-outline-primary js-open-mentor-modal"
                    data-bs-toggle="modal"
                    data-bs-target="#mentorDetailsModal"
                    data-title="Session Attendance Details"
                    data-content="{{ e('Session: ' . ($session->title ?? '-') . "\n" . 'Date: ' . (optional($session->session_date)->format('d-M-Y') ?: '-') . "\n" . 'Group: ' . ($session->group->name ?? '-') . "\n" . 'Faculty: ' . (trim(($session->group->faculty->FIRST_NAME ?? '') . ' ' . ($session->group->faculty->LAST_NAME ?? '')) ?: '-') . "\n" . 'Total Attendance: ' . $total . "\n" . 'Present: ' . $present . "\n" . 'Absent: ' . (int) ($session->absent_count ?? 0) . "\n" . 'Excused: ' . (int) ($session->excused_count ?? 0) . "\n" . 'Attendance %: ' . $pct . '%') }}">
                    View
                  </button>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="10" class="text-center text-muted">No sessions found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
          {{ $sessions->links() }}
        </div>
      </div>

      <div class="card shadow-sm mb-3">
        <div class="card-header">Assignment Information</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Assignment</th>
                <th>Group</th>
                <th>Faculty</th>
                <th>Due Date</th>
                <th>Max Marks</th>
                <th>Submissions</th>
                <th>Graded</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($assignments as $assignment)
              <tr>
                <td>{{ $assignment->title }}</td>
                <td>{{ $assignment->group->name ?? '-' }}</td>
                <td>{{ trim(($assignment->group->faculty->FIRST_NAME ?? '').' '.($assignment->group->faculty->LAST_NAME ?? '')) ?: '-' }}</td>
                <td>{{ optional($assignment->due_date)->format('d-M-Y') ?: '-' }}</td>
                <td>{{ $assignment->max_marks ?? '-' }}</td>
                <td>{{ (int) ($assignment->submissions_count ?? 0) }}</td>
                <td>{{ (int) ($assignment->graded_submissions_count ?? 0) }}</td>
                <td>{{ ucfirst((string) ($assignment->status ?? '-')) }}</td>
                <td>
                  <button type="button"
                    class="btn btn-sm btn-outline-primary js-open-mentor-modal"
                    data-bs-toggle="modal"
                    data-bs-target="#mentorDetailsModal"
                    data-title="Assignment Details"
                    data-content="{{ e('Assignment: ' . ($assignment->title ?? '-') . "\n" . 'Group: ' . ($assignment->group->name ?? '-') . "\n" . 'Faculty: ' . (trim(($assignment->group->faculty->FIRST_NAME ?? '') . ' ' . ($assignment->group->faculty->LAST_NAME ?? '')) ?: '-') . "\n" . 'Due Date: ' . (optional($assignment->due_date)->format('d-M-Y') ?: '-') . "\n" . 'Max Marks: ' . ($assignment->max_marks ?? '-') . "\n" . 'Submissions: ' . (int) ($assignment->submissions_count ?? 0) . "\n" . 'Graded: ' . (int) ($assignment->graded_submissions_count ?? 0) . "\n" . 'Status: ' . ucfirst((string) ($assignment->status ?? '-')) . "\n\n" . 'Description: ' . strip_tags((string) ($assignment->description ?? '-'))) }}">
                    View
                  </button>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="9" class="text-center text-muted">No assignments found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
          {{ $assignments->links() }}
        </div>
      </div>

      <div class="card shadow-sm mb-3">
        <div class="card-header">Students Enrolled in Mentoring Groups</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Group</th>
                <th>Faculty</th>
                <th>Student</th>
                <th>Roll No</th>
                <th>Enrollment Notes</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($enrollments as $enrollment)
              <tr>
                <td>{{ $enrollment->group->name ?? '-' }}</td>
                <td>{{ trim(($enrollment->group->faculty->FIRST_NAME ?? '').' '.($enrollment->group->faculty->LAST_NAME ?? '')) ?: '-' }}</td>
                <td>{{ ($enrollment->student->first_name ?? '') . ' ' . ($enrollment->student->last_name ?? '') }}</td>
                <td>{{ $enrollment->student->roll_no ?? '-' }}</td>
                <td>{{ \Illuminate\Support\Str::limit((string) ($enrollment->notes ?? '-'), 80) }}</td>
                <td>
                  <button type="button"
                    class="btn btn-sm btn-outline-primary js-open-mentor-modal"
                    data-bs-toggle="modal"
                    data-bs-target="#mentorDetailsModal"
                    data-title="Enrollment Details"
                    data-content="{{ e('Group: ' . ($enrollment->group->name ?? '-') . "\n" . 'Faculty: ' . (trim(($enrollment->group->faculty->FIRST_NAME ?? '') . ' ' . ($enrollment->group->faculty->LAST_NAME ?? '')) ?: '-') . "\n" . 'Student: ' . (($enrollment->student->first_name ?? '') . ' ' . ($enrollment->student->last_name ?? '')) . "\n" . 'Roll No: ' . ($enrollment->student->roll_no ?? '-') . "\n\n" . 'Notes: ' . (($enrollment->notes ?? '') !== '' ? $enrollment->notes : '-')) }}">
                    View
                  </button>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center text-muted">No enrolled students found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
          {{ $enrollments->links() }}
        </div>
      </div>

      <div class="card shadow-sm mb-3">
        <div class="card-header">Students with Attendance Marked</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Date</th>
                <th>Session</th>
                <th>Group</th>
                <th>Student</th>
                <th>Roll No</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Action</th>
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
                <td>{{ \Illuminate\Support\Str::limit((string) ($record->remarks ?? '-'), 80) }}</td>
                <td>
                  <button type="button"
                    class="btn btn-sm btn-outline-primary js-open-mentor-modal"
                    data-bs-toggle="modal"
                    data-bs-target="#mentorDetailsModal"
                    data-title="Attendance Record Details"
                    data-content="{{ e('Date: ' . (optional($record->session->session_date ?? null)->format('d-M-Y') ?: '-') . "\n" . 'Session: ' . ($record->session->title ?? '-') . "\n" . 'Group: ' . ($record->session->group->name ?? '-') . "\n" . 'Student: ' . (($record->student->first_name ?? '') . ' ' . ($record->student->last_name ?? '')) . "\n" . 'Roll No: ' . ($record->student->roll_no ?? '-') . "\n" . 'Status: ' . ucfirst((string) ($record->status ?? '-')) . "\n\n" . 'Remarks: ' . (($record->remarks ?? '') !== '' ? $record->remarks : '-')) }}">
                    View
                  </button>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="8" class="text-center text-muted">No attendance-marked records found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
          {{ $attendanceRecords->links() }}
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header">High-risk / Counselling Referral Notes</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Date</th>
                <th>Student</th>
                <th>Category</th>
                <th>Note</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($highRiskNotes as $note)
              <tr>
                <td>{{ optional($note->created_at)->format('d-M-Y') }}</td>
                <td>{{ ($note->student->first_name ?? '') . ' ' . ($note->student->last_name ?? '') }}</td>
                <td>{{ $note->category }}</td>
                <td>{{ \Illuminate\Support\Str::limit($note->note, 120) }}</td>
                <td>
                  <button type="button"
                    class="btn btn-sm btn-outline-primary js-open-mentor-modal"
                    data-bs-toggle="modal"
                    data-bs-target="#mentorDetailsModal"
                    data-title="High-Risk Note"
                    data-content="{{ e('Date: ' . (optional($note->created_at)->format('d-M-Y') ?: '-') . "\n" . 'Student: ' . (($note->student->first_name ?? '') . ' ' . ($note->student->last_name ?? '')) . "\n" . 'Category: ' . ($note->category ?? '-') . "\n\n" . 'Note: ' . (($note->note ?? '') !== '' ? $note->note : '-')) }}">
                    View
                  </button>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center text-muted">No high-risk notes found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="modal fade" id="mentorDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Details</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-break" style="white-space: pre-line;">-</div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>
@include('includes.footer')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('mentorDetailsModal');
    if (!modalEl) return;

    const titleEl = modalEl.querySelector('.modal-title');
    const bodyEl = modalEl.querySelector('.modal-body');

    document.querySelectorAll('.js-open-mentor-modal').forEach(function(button) {
      button.addEventListener('click', function() {
        titleEl.textContent = this.getAttribute('data-title') || 'Details';
        bodyEl.textContent = this.getAttribute('data-content') || '-';
      });
    });
  });
</script>