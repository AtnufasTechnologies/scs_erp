@include('includes.header')
<div class="wrapper">
  @include('student-affairs.sidebar')
  <main class="page-content">
    <div class="container-fluid py-3">
      <h3>Student Discipline</h3>

      <div class="card mb-3 shadow-sm">
        <div class="card-header">Register Complaint / Case</div>
        <div class="card-body">
          <form method="POST" action="{{ route('dean.discipline.store') }}" class="row g-2">
            @csrf
            <div class="col-md-3">
              <select name="student_id" class="dselect-example" required>
                <option value="">Select student</option>
                @foreach($students as $student)
                <option value="{{ $student->id }}">{{ $student->roll_no }} - {{ $student->first_name }} {{ $student->last_name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <select name="severity" class="form-select" required>
                <option value="low">Low</option>
                <option value="medium" selected>Medium</option>
                <option value="high">High</option>
                <option value="critical">Critical</option>
              </select>
            </div>
            <div class="col-md-2"><input type="date" name="incident_date" class="form-control"></div>
            <div class="col-md-5"><input name="summary" class="form-control" placeholder="Case summary" required></div>
            <div class="col-12"><textarea name="details" class="form-control" rows="2" placeholder="Details"></textarea></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Create Case</button></div>
          </form>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header">Discipline Cases</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Case No</th>
                <th>Student</th>
                <th>Severity</th>
                <th>Status</th>
                <th>Incident Date</th>
                <th>Summary</th>
                <th>Actions Taken</th>
                <th>Update Status</th>
                <th>Add Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($cases as $case)
              <tr>
                <td>{{ $case->case_no }}</td>
                <td>{{ ($case->student->first_name ?? '') . ' ' . ($case->student->last_name ?? '') }}</td>
                <td>{{ strtoupper($case->severity) }}</td>
                <td>
                  @if(in_array($case->status, ['resolved', 'closed']))
                  <span class="badge bg-success">{{ strtoupper($case->status) }}</span>
                  @elseif($case->status === 'in_progress')
                  <span class="badge bg-warning text-dark">IN PROGRESS</span>
                  @elseif($case->status === 'dropped')
                  <span class="badge bg-secondary">DROPPED</span>
                  @else
                  <span class="badge bg-danger">OPEN</span>
                  @endif
                </td>
                <td>{{ optional($case->incident_date)->format('d-M-Y') }}</td>
                <td>{{ \Illuminate\Support\Str::limit($case->summary, 100) }}</td>
                <td>
                  <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#actionsModal{{ $case->id }}">
                    View ({{ $case->actions->count() }})
                  </button>
                </td>
                <td>
                  <form method="POST" action="{{ route('dean.discipline.status.update', $case->id) }}" class="d-flex gap-1">
                    @csrf
                    @method('PUT')
                    <select name="status" class="form-select form-select-sm" required>
                      <option value="open" {{ $case->status === 'open' ? 'selected' : '' }}>Open</option>
                      <option value="in_progress" {{ $case->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                      <option value="resolved" {{ $case->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                      <option value="closed" {{ $case->status === 'closed' ? 'selected' : '' }}>Closed</option>
                      <option value="dropped" {{ $case->status === 'dropped' ? 'selected' : '' }}>Dropped</option>
                    </select>
                    <button class="btn btn-sm btn-outline-primary" type="submit">Save</button>
                  </form>
                </td>
                <td>
                  <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addActionModal{{ $case->id }}">Add</button>
                </td>
              </tr>

              <div class="modal fade" id="actionsModal{{ $case->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Actions Taken: {{ $case->case_no }}</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      @forelse($case->actions as $action)
                      <div class="border rounded p-2 mb-2">
                        <div><strong>Type:</strong> {{ strtoupper($action->action_type) }}</div>
                        <div><strong>From:</strong> {{ optional($action->action_from)->format('d-M-Y') ?: '-' }} | <strong>To:</strong> {{ optional($action->action_to)->format('d-M-Y') ?: '-' }}</div>
                        <div><strong>Remarks:</strong> {{ $action->remarks ?: '-' }}</div>
                        @if($action->document_path)
                        <div class="mt-1"><a href="{{ asset('storage/' . $action->document_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">View Document</a></div>
                        @endif
                      </div>
                      @empty
                      <p class="text-muted mb-0">No actions recorded yet.</p>
                      @endforelse
                    </div>
                  </div>
                </div>
              </div>

              <div class="modal fade" id="addActionModal{{ $case->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Add Action: {{ $case->case_no }}</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('dean.discipline.actions.store', $case->id) }}" enctype="multipart/form-data">
                      @csrf
                      <div class="modal-body row g-2">
                        <div class="col-md-4"><input name="action_type" class="form-control" placeholder="Action type (warning/suspension)" required></div>
                        <div class="col-md-4"><input type="date" name="action_from" class="form-control"></div>
                        <div class="col-md-4"><input type="date" name="action_to" class="form-control"></div>
                        <div class="col-md-4">
                          <select name="status_after_action" class="form-select">
                            <option value="">Keep current status</option>
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="closed">Closed</option>
                            <option value="dropped">Dropped</option>
                          </select>
                        </div>
                        <div class="col-md-8"><input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
                        <div class="col-12"><textarea name="remarks" class="form-control" rows="3" placeholder="Action remarks"></textarea></div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary">Save Action</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              @empty
              <tr>
                <td colspan="9" class="text-center text-muted">No discipline cases found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
          {{ $cases->links() }}
        </div>
      </div>
    </div>
  </main>
</div>
@include('includes.footer')