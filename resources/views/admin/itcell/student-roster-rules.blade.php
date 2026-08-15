@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">ITCELL Student Roster Rules Manager</h4>
  </div>

  @if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if(session('error'))
  <div class="alert alert-danger">{{ session('error') }}</div>
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

  <div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
      <h5 class="mb-0">Create New Rule Mapping</h5>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('itcell.student-roster-rules.store') }}" class="row g-3 align-items-end">
        @csrf

        <div class="col-md-4">
          <label class="form-label">Rule Master</label>
          <select name="rule_id" class="dselect-example" required>
            <option value="">Select rule</option>
            @foreach(($ruleMasters ?? collect()) as $ruleMaster)
            <option value="{{ (int) $ruleMaster->id }}" {{ (string) old('rule_id') === (string) $ruleMaster->id ? 'selected' : '' }}>
              {{ $ruleMaster->rule_code ?? 'N/A' }} - {{ $ruleMaster->rule_name ?? 'Unnamed Rule' }}
            </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Academic Pathway</label>
          <select name="academic_pathway_id" class="form-select" required>
            <option value="">Select pathway</option>
            @foreach(($pathways ?? collect()) as $pathway)
            <option value="{{ (int) $pathway->id }}" {{ (string) old('academic_pathway_id') === (string) $pathway->id ? 'selected' : '' }}>
              {{ $pathway->name ?? 'Unnamed Pathway' }}
            </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Degree Track (optional)</label>
          <select name="degree_track_id" class="form-select">
            <option value="">All / Generic</option>
            @foreach(($degreeTracks ?? collect()) as $track)
            <option value="{{ (int) $track->id }}" {{ (string) old('degree_track_id') === (string) $track->id ? 'selected' : '' }}>
              {{ $track->name ?? 'Unnamed Track' }}
            </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Priority</label>
          <input type="number" min="1" max="100000" name="priority" class="form-control" value="{{ old('priority', 100) }}" required>
        </div>

        <div class="col-md-3">
          <label class="form-label">Delivery Type</label>
          <select name="delivery_type" class="form-select" required>
            @foreach(($deliveryTypeOptions ?? []) as $option)
            <option value="{{ $option }}" {{ strtoupper((string) old('delivery_type', '')) === strtoupper((string) $option) ? 'selected' : '' }}>{{ $option }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Selection Type</label>
          <select name="selection_type" class="form-select">
            <option value="">Any / Not Required</option>
            @foreach(($selectionTypeOptions ?? []) as $option)
            <option value="{{ $option }}" {{ strtoupper((string) old('selection_type', '')) === strtoupper((string) $option) ? 'selected' : '' }}>{{ $option }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Roster Source</label>
          <select name="roster_source" class="form-select" required>
            @foreach(($rosterSourceOptions ?? []) as $option)
            <option value="{{ $option }}" {{ strtoupper((string) old('roster_source', 'CURRICULUM')) === strtoupper((string) $option) ? 'selected' : '' }}>{{ $option }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Major Restriction</label>
          <select name="major_restriction" class="form-select" required>
            @foreach(($majorRestrictionOptions ?? []) as $option)
            <option value="{{ $option }}" {{ strtoupper((string) old('major_restriction', 'NONE')) === strtoupper((string) $option) ? 'selected' : '' }}>{{ $option }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Semester Scope</label>
          <select name="semester_scope" class="form-select" required>
            @foreach(($semesterScopeOptions ?? []) as $option)
            <option value="{{ $option }}" {{ strtoupper((string) old('semester_scope', 'SAME')) === strtoupper((string) $option) ? 'selected' : '' }}>{{ $option }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Batch Scope</label>
          <select name="batch_scope" class="form-select" required>
            @foreach(($batchScopeOptions ?? []) as $option)
            <option value="{{ $option }}" {{ strtoupper((string) old('batch_scope', 'SAME')) === strtoupper((string) $option) ? 'selected' : '' }}>{{ $option }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Program Scope</label>
          <select name="program_scope" class="form-select" required>
            @foreach(($programScopeOptions ?? []) as $option)
            <option value="{{ $option }}" {{ strtoupper((string) old('program_scope', 'MULTIPLE')) === strtoupper((string) $option) ? 'selected' : '' }}>{{ $option }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Specialization Scope</label>
          <select name="specialization_scope" class="form-select" required>
            @foreach(($specializationScopeOptions ?? []) as $option)
            <option value="{{ $option }}" {{ strtoupper((string) old('specialization_scope', 'ANY')) === strtoupper((string) $option) ? 'selected' : '' }}>{{ $option }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Selection Required</label>
          <select name="student_selection_required" class="form-select" required>
            <option value="0" {{ (string) old('student_selection_required', '0') === '0' ? 'selected' : '' }}>No</option>
            <option value="1" {{ (string) old('student_selection_required', '0') === '1' ? 'selected' : '' }}>Yes</option>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Teaching Group Override</label>
          <select name="teaching_group_override" class="form-select" required>
            <option value="0" {{ (string) old('teaching_group_override', '0') === '0' ? 'selected' : '' }}>No</option>
            <option value="1" {{ (string) old('teaching_group_override', '0') === '1' ? 'selected' : '' }}>Yes</option>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Active</label>
          <select name="is_active" class="form-select" required>
            <option value="1" {{ (string) old('is_active', '1') === '1' ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ (string) old('is_active', '1') === '0' ? 'selected' : '' }}>No</option>
          </select>
        </div>

        <div class="col-md-12">
          <label class="form-label">Notes</label>
          <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes...">{{ old('notes') }}</textarea>
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-primary">Create Rule Mapping</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Configured Student Roster Rule Mappings</h5>
      <span class="badge bg-secondary">{{ ($mappings ?? collect())->count() }} Mapping(s)</span>
    </div>
    <div class="card-body">
      @if(($mappings ?? collect())->isEmpty())
      <div class="alert alert-info mb-0">No student roster rule mappings found.</div>
      @else
      <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 60px;">#</th>
              <th>Rule</th>
              <th>Pathway / Track</th>
              <th>Delivery / Selection</th>
              <th>Source</th>
              <th>Scope</th>
              <th style="width: 100px;">Priority</th>
              <th style="width: 90px;">Active</th>
              <th style="width: 220px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach(($mappings ?? collect()) as $index => $mapping)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>
                <div><strong>{{ $mapping->rule->rule_code ?? 'N/A' }}</strong></div>
                <div class="small text-muted">{{ $mapping->rule->rule_name ?? 'Unknown Rule' }}</div>
              </td>
              <td>
                <div>{{ $mapping->academicPathway->name ?? '-' }}</div>
                <div class="small text-muted">{{ $mapping->degreeTrack->name ?? 'All Tracks' }}</div>
              </td>
              <td>
                <div><span class="badge bg-info text-dark">{{ strtoupper((string) ($mapping->delivery_type ?? '-')) }}</span></div>
                <div class="small text-muted">{{ strtoupper((string) ($mapping->selection_type ?? 'ANY')) }}</div>
              </td>
              <td>{{ strtoupper((string) ($mapping->roster_source ?? '-')) }}</td>
              <td>
                <div class="small">Semester: {{ strtoupper((string) ($mapping->semester_scope ?? '-')) }}</div>
                <div class="small">Batch: {{ strtoupper((string) ($mapping->batch_scope ?? '-')) }}</div>
                <div class="small">Program: {{ strtoupper((string) ($mapping->program_scope ?? '-')) }}</div>
                <div class="small">Spec: {{ strtoupper((string) ($mapping->specialization_scope ?? '-')) }}</div>
              </td>
              <td>{{ (int) ($mapping->priority ?? 0) }}</td>
              <td>
                <span class="badge {{ (int) ($mapping->is_active ?? 0) === 1 ? 'bg-success' : 'bg-secondary' }}">
                  {{ (int) ($mapping->is_active ?? 0) === 1 ? 'Yes' : 'No' }}
                </span>
              </td>
              <td>
                <div class="d-flex gap-2 flex-wrap">
                  <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editRosterRuleModal{{ (int) $mapping->id }}">Edit</button>
                  <form method="POST" action="{{ route('itcell.student-roster-rules.destroy', (int) $mapping->id) }}" onsubmit="return confirm('Delete this student roster rule mapping?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </div>

                <div class="modal fade" id="editRosterRuleModal{{ (int) $mapping->id }}" tabindex="-1" aria-labelledby="editRosterRuleModalLabel{{ (int) $mapping->id }}" aria-hidden="true">
                  <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                      <form method="POST" action="{{ route('itcell.student-roster-rules.update', (int) $mapping->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="modal-header">
                          <h5 class="modal-title" id="editRosterRuleModalLabel{{ (int) $mapping->id }}">Edit Rule Mapping #{{ (int) $mapping->id }}</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                          <div class="row g-2">
                            <div class="col-md-6">
                              <label class="form-label">Rule Master</label>
                              <select name="rule_id" class="form-select form-select-sm" required>
                                @foreach(($ruleMasters ?? collect()) as $ruleMaster)
                                <option value="{{ (int) $ruleMaster->id }}" {{ (int) ($mapping->rule_id ?? 0) === (int) $ruleMaster->id ? 'selected' : '' }}>
                                  {{ $ruleMaster->rule_code ?? 'N/A' }} - {{ $ruleMaster->rule_name ?? 'Unnamed Rule' }}
                                </option>
                                @endforeach
                              </select>
                            </div>

                            <div class="col-md-3">
                              <label class="form-label">Academic Pathway</label>
                              <select name="academic_pathway_id" class="form-select form-select-sm" required>
                                @foreach(($pathways ?? collect()) as $pathway)
                                <option value="{{ (int) $pathway->id }}" {{ (int) ($mapping->academic_pathway_id ?? 0) === (int) $pathway->id ? 'selected' : '' }}>{{ $pathway->name ?? 'Unnamed Pathway' }}</option>
                                @endforeach
                              </select>
                            </div>

                            <div class="col-md-3">
                              <label class="form-label">Degree Track</label>
                              <select name="degree_track_id" class="form-select form-select-sm">
                                <option value="">All / Generic</option>
                                @foreach(($degreeTracks ?? collect()) as $track)
                                <option value="{{ (int) $track->id }}" {{ (int) ($mapping->degree_track_id ?? 0) === (int) $track->id ? 'selected' : '' }}>{{ $track->name ?? 'Unnamed Track' }}</option>
                                @endforeach
                              </select>
                            </div>

                            <div class="col-md-2">
                              <label class="form-label">Delivery</label>
                              <select name="delivery_type" class="form-select form-select-sm" required>
                                @foreach(($deliveryTypeOptions ?? []) as $option)
                                <option value="{{ $option }}" {{ strtoupper((string) ($mapping->delivery_type ?? '')) === strtoupper((string) $option) ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                              </select>
                            </div>

                            <div class="col-md-2">
                              <label class="form-label">Selection</label>
                              <select name="selection_type" class="form-select form-select-sm">
                                <option value="">Any</option>
                                @foreach(($selectionTypeOptions ?? []) as $option)
                                <option value="{{ $option }}" {{ strtoupper((string) ($mapping->selection_type ?? '')) === strtoupper((string) $option) ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                              </select>
                            </div>

                            <div class="col-md-2">
                              <label class="form-label">Source</label>
                              <select name="roster_source" class="form-select form-select-sm" required>
                                @foreach(($rosterSourceOptions ?? []) as $option)
                                <option value="{{ $option }}" {{ strtoupper((string) ($mapping->roster_source ?? '')) === strtoupper((string) $option) ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                              </select>
                            </div>

                            <div class="col-md-2">
                              <label class="form-label">Semester Scope</label>
                              <select name="semester_scope" class="form-select form-select-sm" required>
                                @foreach(($semesterScopeOptions ?? []) as $option)
                                <option value="{{ $option }}" {{ strtoupper((string) ($mapping->semester_scope ?? '')) === strtoupper((string) $option) ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                              </select>
                            </div>

                            <div class="col-md-2">
                              <label class="form-label">Batch Scope</label>
                              <select name="batch_scope" class="form-select form-select-sm" required>
                                @foreach(($batchScopeOptions ?? []) as $option)
                                <option value="{{ $option }}" {{ strtoupper((string) ($mapping->batch_scope ?? '')) === strtoupper((string) $option) ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                              </select>
                            </div>

                            <div class="col-md-2">
                              <label class="form-label">Program Scope</label>
                              <select name="program_scope" class="form-select form-select-sm" required>
                                @foreach(($programScopeOptions ?? []) as $option)
                                <option value="{{ $option }}" {{ strtoupper((string) ($mapping->program_scope ?? '')) === strtoupper((string) $option) ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                              </select>
                            </div>

                            <div class="col-md-2">
                              <label class="form-label">Specialization Scope</label>
                              <select name="specialization_scope" class="form-select form-select-sm" required>
                                @foreach(($specializationScopeOptions ?? []) as $option)
                                <option value="{{ $option }}" {{ strtoupper((string) ($mapping->specialization_scope ?? '')) === strtoupper((string) $option) ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                              </select>
                            </div>

                            <div class="col-md-2">
                              <label class="form-label">Major Restriction</label>
                              <select name="major_restriction" class="form-select form-select-sm" required>
                                @foreach(($majorRestrictionOptions ?? []) as $option)
                                <option value="{{ $option }}" {{ strtoupper((string) ($mapping->major_restriction ?? '')) === strtoupper((string) $option) ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                              </select>
                            </div>

                            <div class="col-md-1">
                              <label class="form-label">Priority</label>
                              <input type="number" min="1" max="100000" name="priority" class="form-control form-control-sm" value="{{ (int) ($mapping->priority ?? 100) }}" required>
                            </div>

                            <div class="col-md-2">
                              <label class="form-label">Selection Required</label>
                              <select name="student_selection_required" class="form-select form-select-sm" required>
                                <option value="0" {{ (int) ($mapping->student_selection_required ?? 0) === 0 ? 'selected' : '' }}>No</option>
                                <option value="1" {{ (int) ($mapping->student_selection_required ?? 0) === 1 ? 'selected' : '' }}>Yes</option>
                              </select>
                            </div>

                            <div class="col-md-2">
                              <label class="form-label">TG Override</label>
                              <select name="teaching_group_override" class="form-select form-select-sm" required>
                                <option value="0" {{ (int) ($mapping->teaching_group_override ?? 0) === 0 ? 'selected' : '' }}>No</option>
                                <option value="1" {{ (int) ($mapping->teaching_group_override ?? 0) === 1 ? 'selected' : '' }}>Yes</option>
                              </select>
                            </div>

                            <div class="col-md-2">
                              <label class="form-label">Active</label>
                              <select name="is_active" class="form-select form-select-sm" required>
                                <option value="1" {{ (int) ($mapping->is_active ?? 0) === 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ (int) ($mapping->is_active ?? 0) === 0 ? 'selected' : '' }}>Inactive</option>
                              </select>
                            </div>

                            <div class="col-md-12">
                              <label class="form-label">Notes</label>
                              <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Optional notes...">{{ (string) ($mapping->notes ?? '') }}</textarea>
                            </div>
                          </div>
                        </div>

                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" class="btn btn-success btn-sm">Save Changes</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>
</div>

@include('includes.footer')