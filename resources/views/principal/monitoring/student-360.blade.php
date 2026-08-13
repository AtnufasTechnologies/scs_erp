@include('includes.header')
<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="container-fluid py-3">
      <div class="card border-0 shadow-lg mb-4" style="background: linear-gradient(120deg, #bfe6f1 0%, #1d4ed8 100%);">
        <div class="card-body text-white py-4">
          <h3 class="mb-1">Student 360 Monitor</h3>
          <p class="mb-0 opacity-75">Read-only behavioural, academic, counselling, and discipline overview for monitoring.</p>
        </div>
      </div>

      <div class="card mb-3 shadow-sm border-0">
        <div class="card-body">
          <form method="GET" action="{{ route('principal.monitoring.student360') }}" class="row g-2 align-items-end">
            <div class="col-md-6">
              <label class="form-label">Select Student</label>
              <select name="student_id" class="dselect-example" required>
                <option value="">Select student</option>
                @foreach($students as $student)
                <option value="{{ $student->id }}" {{ (int) request('student_id') === (int) $student->id ? 'selected' : '' }}>
                  {{ $student->roll_no }} - {{ $student->first_name }} {{ $student->last_name }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Load Profile</button></div>
          </form>
        </div>
      </div>

      @if($profile)
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <div class="card border-0 shadow-sm">
            <div class="card-body"><small class="text-muted">Attendance</small>
              <h4 class="mb-0">{{ $profile['attendance_pct'] }}%</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm">
            <div class="card-body"><small class="text-muted">Mentoring Notes</small>
              <h4 class="mb-0">{{ $profile['mentoring_notes']->count() }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm">
            <div class="card-body"><small class="text-muted">Discipline Cases</small>
              <h4 class="mb-0">{{ $profile['discipline_cases']->count() }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm">
            <div class="card-body"><small class="text-muted">Counselling Cases</small>
              <h4 class="mb-0">{{ $profile['counselling_cases']->count() }}</h4>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white">Student Snapshot</div>
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
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">Club and Council Involvement</div>
            <div class="card-body table-responsive">
              <table class="table table-sm table-bordered mb-2">
                <thead>
                  <tr>
                    <th>Club</th>
                    <th>Role</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($profile['club_memberships'] as $clubMember)
                  <tr>
                    <td>{{ $clubMember->club->name ?? '-' }}</td>
                    <td>{{ $clubMember->role_title ?? '-' }}</td>
                    <td>{{ ucfirst((string) ($clubMember->status ?? '-')) }}</td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="3" class="text-center text-muted">No club memberships</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>

              <table class="table table-sm table-bordered mb-0">
                <thead>
                  <tr>
                    <th>Council</th>
                    <th>Role</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($profile['council_roles'] as $role)
                  <tr>
                    <td>{{ $role->council->title ?? '-' }}</td>
                    <td>{{ $role->role_title ?? '-' }}</td>
                    <td>{{ ucfirst((string) ($role->status ?? '-')) }}</td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="3" class="text-center text-muted">No council roles</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">Counselling and Discipline</div>
            <div class="card-body table-responsive">
              <table class="table table-sm table-bordered mb-2">
                <thead>
                  <tr>
                    <th>Discipline Case</th>
                    <th>Status</th>
                    <th>Severity</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($profile['discipline_cases'] as $case)
                  <tr>
                    <td>{{ $case->case_no }}</td>
                    <td>{{ strtoupper($case->status ?? '-') }}</td>
                    <td>{{ strtoupper($case->severity ?? '-') }}</td>
                    <td>{{ $case->actions->count() }}</td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="4" class="text-center text-muted">No discipline cases</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>

              <table class="table table-sm table-bordered mb-0">
                <thead>
                  <tr>
                    <th>Counselling Case</th>
                    <th>Status</th>
                    <th>Risk</th>
                    <th>Followups</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($profile['counselling_cases'] as $case)
                  <tr>
                    <td>{{ $case->case_no }}</td>
                    <td>{{ strtoupper($case->status ?? '-') }}</td>
                    <td>{{ strtoupper($case->risk_level ?? '-') }}</td>
                    <td>{{ $case->followups->count() }}</td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="4" class="text-center text-muted">No counselling cases</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      @endif
    </div>
  </main>
</div>
@include('includes.footer')