<?php

use App\Models\AcademicPathwayMaster;
use App\Models\DegreeTrackMaster;
use App\Models\StudentProgram;

$programs = StudentProgram::latest()->get();
$pathways = $pathways ?? AcademicPathwayMaster::orderBy('name')->get();
$degreeTracks = $degreeTracks ?? DegreeTrackMaster::orderBy('name')->get();
$programMetaMap = $programs->keyBy('id');
$pathwayNameMap = $pathways->pluck('name', 'id');
$degreeTrackNameMap = $degreeTracks->pluck('name', 'id');
$programConfigGroups = collect($data ?? [])->groupBy('program_id');

?>
@include('includes.header')
@include('admin.sidebar')

<style>
  .program-scroll-box {
    max-height: 280px;
    overflow-y: auto;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 10px;
    background: #fff;
  }

  .engine-card {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(17, 24, 39, 0.06);
    background: #fff;
    height: 100%;
  }

  .engine-card-head {
    border-bottom: 1px solid #eef2f7;
    padding-bottom: 8px;
    margin-bottom: 10px;
  }

  .engine-chip {
    display: inline-block;
    font-size: 0.78rem;
    border-radius: 20px;
    padding: 4px 10px;
    margin-right: 6px;
    margin-bottom: 6px;
    background: #f3f4f6;
    color: #374151;
  }

  .engine-empty {
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    text-align: center;
    padding: 24px;
    color: #64748b;
  }
</style>

<div class="container-fluid p-4">

  <h4>Program Semester <span class="text-danger"> Engine</span> </h4>

  <!-- Button trigger modal -->
  <button class="cst-button mb-3" style="--clr: #21d9c7ff;" data-bs-toggle="modal" data-bs-target="#exampleModal">
    <span class="button-decor"></span>
    <div class="button-content">
      <div class="button__icon">
        <i class="fa fa-plus-circle"></i>
      </div>
      <span class="button__text">Add Settings</span>
    </div>
  </button>

  <div class="alert alert-warning">All Semester Changes like <strong>Dual to Single Major </strong> happens due to the settings made in here.
    Here you basically explain the system what to do when someone get promoted to a semester.
  </div>

  <!-- Modal -->
  <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Assign Settings to Programs in Single Go</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('itcell.semester.engine.store')}}" method="post" id="semesterEngineBulkForm">
          @csrf
          <div class="modal-body">
            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="mb-0">Select Multiple Program *</label>
                <div class="form-check m-0">
                  <input class="form-check-input" type="checkbox" id="selectAllPrograms">
                  <label class="form-check-label" for="selectAllPrograms">Select All Programs</label>
                </div>
              </div>

              <div class="mb-2">
                <input type="text" class="form-control form-control-sm" id="programSearchInput" placeholder="Search program by code or name">
              </div>

              <div class="program-scroll-box" id="programCheckboxList">
                @foreach ($programs as $program)
                <div class="form-check mb-1">
                  <input class="form-check-input program-checkbox" type="checkbox" name="programs[]" value="{{$program->id}}" id="program{{$program->id}}">
                  <label class="form-check-label" for="program{{$program->id}}">{{$program->code}} - {{$program->name}} | {{$program->campus_id == 1?'Sonada':'Siliguri'}}</label>
                </div>
                @endforeach
              </div>
              <small class="text-muted">Tip: Use Select All, then uncheck any program you want to exclude.</small>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
              <label class="mb-0">Configuration Rows *</label>
              <button type="button" class="btn btn-outline-success btn-sm" id="addSemesterRuleRow">
                <i class="fa fa-plus"></i> Add More
              </button>
            </div>

            <div id="semesterRuleRows">
              <div class="row g-2 border rounded p-2 mb-2 semester-rule-row">
                <div class="col-md-3">
                  <label class="small">Effective Semester *</label>
                  <input type="number" class="form-control" name="configs[0][effective_semester]" min="1" max="20" required>
                </div>
                <div class="col-md-4">
                  <label class="small">Allowed Pathway *</label>
                  <select class="form-control" name="configs[0][allowed_pathway_id]" required>
                    <option value="">--Select--</option>
                    @foreach($pathways as $pathway)
                    <option value="{{$pathway->id}}" data-name="{{strtolower($pathway->name)}}">{{$pathway->name}}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="small">Allowed Degree Track *</label>
                  <select class="form-control" name="configs[0][allowed_degree_track_id]" required>
                    <option value="">--Select--</option>
                    @foreach($degreeTracks as $track)
                    <option value="{{$track->id}}" data-name="{{strtolower($track->name)}}">{{$track->name}}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                  <button type="button" class="btn btn-outline-danger btn-sm remove-rule-row" disabled>
                    <i class="fa fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success" id="semesterEngineSubmitBtn">
              <span id="semesterEngineSubmitText">Submit</span>
              <span id="semesterEngineSubmittingText" class="d-none">
                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                Submitting...
              </span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
    <h5 class="mb-0">Configured Program Rules</h5>
    <div style="max-width: 360px; width: 100%;">
      <input type="text" id="configCardSearchInput" class="form-control form-control-sm" placeholder="Search by program, semester, pathway or track">
    </div>
  </div>

  @if($programConfigGroups->count() > 0)
  <div class="row g-3" id="configCardGrid">
    @foreach($programConfigGroups as $programId => $configs)
    @php
    $first = $configs->first();
    $programCode = strtoupper($first->coode ?? 'NA');
    $programTitle = $first->title ?? 'Unknown Program';
    $programMeta = $programMetaMap[$programId] ?? null;
    $campusName = ((int) ($programMeta->campus_id ?? 0) === 1) ? 'Sonada' : (((int) ($programMeta->campus_id ?? 0) === 2) ? 'Siliguri' : 'Unknown Campus');
    $searchChunks = [$programCode, $programTitle, $campusName];
    @endphp

    <div class="col-12 col-md-6 col-xl-4 config-program-card" data-config-card>
      <div class="engine-card p-3">
        <div class="engine-card-head">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h6 class="mb-1">{{$programTitle }} <span class="badge badge-info">{{$campusName}}</span></h6>
              <div class="text-muted small">Code: {{$programCode}}</div>
            </div>
            <span class="badge bg-primary">{{$configs->count()}} Rule(s)</span>
          </div>
        </div>

        <div>
          @foreach($configs->sortBy('effective_semester') as $cfg)
          @php
          $pathwayName = $pathwayNameMap[$cfg->allowed_pathway_id] ?? ('Pathway #' . $cfg->allowed_pathway_id);
          $trackName = $degreeTrackNameMap[$cfg->allowed_degree_track_id] ?? ('Track #' . $cfg->allowed_degree_track_id);
          $searchChunks[] = 'semester ' . $cfg->effective_semester;
          $searchChunks[] = $pathwayName;
          $searchChunks[] = $trackName;
          @endphp
          <div class="mb-2 pb-2 border-bottom">
            <div class="mb-1">
              <span class="engine-chip">Semester {{$cfg->effective_semester}}</span>
              <span class="engine-chip">{{$pathwayName}}</span>
              <span class="engine-chip">{{$trackName}}</span>
            </div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editCfg{{$cfg->id}}">
                <i class="fa fa-edit"></i> Edit
              </button>
              <form action="{{route('itcell.semester.engine.delete', $cfg->id)}}" method="post" onsubmit="return confirm('Delete this configuration rule?');">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                  <i class="fa fa-trash"></i> Delete
                </button>
              </form>
            </div>
          </div>

          <div class="modal fade" id="editCfg{{$cfg->id}}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Edit Semester Rule</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{route('itcell.semester.engine.update', $cfg->id)}}" method="post">
                  @csrf
                  <div class="modal-body">
                    <div class="row g-3">
                      <div class="col-md-6">
                        <label class="form-label">Program *</label>
                        <select name="program_id" class="form-control" required>
                          @foreach($programs as $program)
                          <option value="{{$program->id}}" {{$cfg->program_id == $program->id ? 'selected' : ''}}>{{$program->code}} - {{$program->name}}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Effective Semester *</label>
                        <input type="number" name="effective_semester" value="{{$cfg->effective_semester}}" class="form-control" min="1" max="20" required>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Allowed Pathway *</label>
                        <select name="allowed_pathway_id" class="form-control" required>
                          @foreach($pathways as $pathway)
                          <option value="{{$pathway->id}}" {{$cfg->allowed_pathway_id == $pathway->id ? 'selected' : ''}}>{{$pathway->name}}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Allowed Degree Track *</label>
                        <select name="allowed_degree_track_id" class="form-control" required>
                          @foreach($degreeTracks as $track)
                          <option value="{{$track->id}}" {{$cfg->allowed_degree_track_id == $track->id ? 'selected' : ''}}>{{$track->name}}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
      <span class="d-none config-search-text">{{strtolower(implode(' ', $searchChunks))}}</span>
    </div>
    @endforeach
  </div>
  @else
  <div class="engine-empty">No semester engine configurations found.</div>
  @endif

</div>



@include('includes.footer')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var semesterEngineForm = document.getElementById('semesterEngineBulkForm');
    var semesterEngineSubmitBtn = document.getElementById('semesterEngineSubmitBtn');
    var semesterEngineSubmitText = document.getElementById('semesterEngineSubmitText');
    var semesterEngineSubmittingText = document.getElementById('semesterEngineSubmittingText');
    var rowsContainer = document.getElementById('semesterRuleRows');
    var addRowBtn = document.getElementById('addSemesterRuleRow');
    var selectAllPrograms = document.getElementById('selectAllPrograms');
    var programSearchInput = document.getElementById('programSearchInput');
    var programCheckboxList = document.getElementById('programCheckboxList');
    var programCheckboxes = Array.from(document.querySelectorAll('.program-checkbox'));
    var configCardSearchInput = document.getElementById('configCardSearchInput');
    var configCards = Array.from(document.querySelectorAll('.config-program-card'));
    var rowIndex = 1;

    function escapeHtml(text) {
      return (text || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function buildSelectOptions(selectName, selectedValue) {
      var html = '<option value="">--Select--</option>';

      var sourceSelect = document.querySelector('select[name="' + selectName + '"]');
      if (!sourceSelect) {
        return html;
      }

      sourceSelect.querySelectorAll('option').forEach(function(option) {
        var value = option.value || '';
        var label = option.textContent || '';
        var selected = String(value) === String(selectedValue) ? ' selected' : '';
        html += '<option value="' + escapeHtml(value) + '"' + selected + '>' + escapeHtml(label) + '</option>';
      });

      return html;
    }

    function buildRow(index, semesterVal, pathwayVal, degreeVal) {
      var pathwayOptions = buildSelectOptions('configs[0][allowed_pathway_id]', pathwayVal);
      var degreeOptions = buildSelectOptions('configs[0][allowed_degree_track_id]', degreeVal);

      return '<div class="row g-2 border rounded p-2 mb-2 semester-rule-row">' +
        '<div class="col-md-3"><label class="small">Effective Semester *</label><input type="number" class="form-control" name="configs[' + index + '][effective_semester]" min="1" max="20" required value="' + escapeHtml(String(semesterVal || '')) + '"></div>' +
        '<div class="col-md-4"><label class="small">Allowed Pathway *</label><select class="form-control" name="configs[' + index + '][allowed_pathway_id]" required>' + pathwayOptions + '</select></div>' +
        '<div class="col-md-4"><label class="small">Allowed Degree Track *</label><select class="form-control" name="configs[' + index + '][allowed_degree_track_id]" required>' + degreeOptions + '</select></div>' +
        '<div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-outline-danger btn-sm remove-rule-row"><i class="fa fa-trash"></i></button></div>' +
        '</div>';
    }

    function addRuleRow(semesterVal, pathwayVal, degreeVal) {
      rowsContainer.insertAdjacentHTML('beforeend', buildRow(rowIndex, semesterVal, pathwayVal, degreeVal));
      rowIndex++;
    }

    if (addRowBtn) {
      addRowBtn.addEventListener('click', function() {
        addRuleRow('', '', '');
      });
    }

    if (rowsContainer) {
      rowsContainer.addEventListener('click', function(e) {
        var removeBtn = e.target.closest('.remove-rule-row');
        if (!removeBtn) {
          return;
        }
        var row = removeBtn.closest('.semester-rule-row');
        if (row) {
          row.remove();
        }
      });
    }

    function syncSelectAllState() {
      if (!selectAllPrograms || !programCheckboxes.length) {
        return;
      }
      var checkedCount = programCheckboxes.filter(function(cb) {
        return cb.checked;
      }).length;

      selectAllPrograms.checked = checkedCount === programCheckboxes.length;
      selectAllPrograms.indeterminate = checkedCount > 0 && checkedCount < programCheckboxes.length;
    }

    if (selectAllPrograms && programCheckboxes.length) {
      selectAllPrograms.addEventListener('change', function() {
        programCheckboxes.forEach(function(cb) {
          cb.checked = selectAllPrograms.checked;
        });
        syncSelectAllState();
      });

      programCheckboxes.forEach(function(cb) {
        cb.addEventListener('change', syncSelectAllState);
      });
    }

    if (programSearchInput && programCheckboxList) {
      programSearchInput.addEventListener('input', function() {
        var term = (programSearchInput.value || '').trim().toLowerCase();
        var rows = programCheckboxList.querySelectorAll('.form-check');

        rows.forEach(function(row) {
          var text = (row.textContent || '').toLowerCase();
          row.style.display = text.indexOf(term) !== -1 ? '' : 'none';
        });
      });
    }

    if (semesterEngineForm && semesterEngineSubmitBtn) {
      semesterEngineForm.addEventListener('submit', function(e) {
        if (semesterEngineSubmitBtn.getAttribute('data-submitting') === '1') {
          e.preventDefault();
          return;
        }

        semesterEngineSubmitBtn.setAttribute('data-submitting', '1');
        semesterEngineSubmitBtn.disabled = true;

        if (semesterEngineSubmitText) {
          semesterEngineSubmitText.classList.add('d-none');
        }
        if (semesterEngineSubmittingText) {
          semesterEngineSubmittingText.classList.remove('d-none');
        }
      });
    }

    if (configCardSearchInput && configCards.length) {
      configCardSearchInput.addEventListener('input', function() {
        var term = (configCardSearchInput.value || '').trim().toLowerCase();
        configCards.forEach(function(card) {
          var hiddenText = card.querySelector('.config-search-text');
          var haystack = hiddenText ? hiddenText.textContent.toLowerCase() : '';
          card.style.display = haystack.indexOf(term) !== -1 ? '' : 'none';
        });
      });
    }

  });
</script>