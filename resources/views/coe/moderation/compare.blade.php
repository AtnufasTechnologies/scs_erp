@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Moderation</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.moderation-duties.index') }}">Moderation Duties</a></li>
            <li class="breadcrumb-item active" aria-current="page">Compare Marks</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid py-4">
      <!-- Page Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-6">
                  <h3 class="text-dark fw-bold mb-2"><i class="fas fa-balance-scale me-2"></i>Compare & Moderate Marks</h3>
                  <p class="text-muted mb-0">Compare evaluator vs moderator marks, highlight differences and adjust</p>
                </div>
                <div class="col-md-6 text-md-end">
                  <a href="{{ route('admin.moderation-duties.index') }}" class="btn btn-info">
                    <i class="fas fa-arrow-left me-2"></i>Back to Duties
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <!-- Filter Card -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light fw-semibold">
          <i class="fas fa-filter me-2"></i>Select Exam Session & Subject
        </div>
        <div class="card-body">
          <form method="GET" action="{{ route('admin.moderation-duties.compare') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Exam Session <span class="text-danger">*</span></label>
              <select name="exam_session_id" id="sessionSelect" class="form-select" required>
                <option value="">Select Session</option>
                @foreach($sessions as $session)
                <option value="{{ $session->id }}" {{ $selectedSession == $session->id ? 'selected' : '' }}>
                  {{ $session->name }} ({{ $session->academic_year ?? '' }})
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
              <select name="erp_subject_id" id="subjectFilterSelect" class="form-select" {{ $subjects->isEmpty() ? 'disabled' : '' }}>
                <option value="">{{ $subjects->isEmpty() ? 'Select Session first' : 'Select Subject' }}</option>
                @foreach($subjects as $subject)
                <option value="{{ $subject->erp_subject_id }}" {{ $selectedSubject == $subject->erp_subject_id ? 'selected' : '' }}>
                  {{ $subject->subject_code }} - {{ $subject->name }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search me-1"></i>Load Marks</button>
              <a href="{{ route('admin.moderation-duties.compare') }}" class="btn btn-outline-secondary"><i class="fas fa-undo me-1"></i>Reset</a>
            </div>
          </form>
        </div>
      </div>

      @if($selectedSession && $selectedSubject)
      <!-- Stats Cards -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                  <i class="fas fa-users text-primary fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Total Students</div>
                  <div class="fs-4 fw-bold">{{ $stats['total'] }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle p-3 me-3">
                  <i class="fas fa-check-double text-success fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Moderated</div>
                  <div class="fs-4 fw-bold">{{ $stats['moderated'] }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-danger bg-opacity-10 rounded-circle p-3 me-3">
                  <i class="fas fa-exclamation-triangle text-danger fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Flagged (>&thinsp;{{ $threshold }})</div>
                  <div class="fs-4 fw-bold text-danger">{{ $stats['flagged'] }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-info bg-opacity-10 rounded-circle p-3 me-3">
                  <i class="fas fa-chart-line text-info fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Avg Deviation</div>
                  <div class="fs-4 fw-bold">{{ $stats['avg_diff'] }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body d-flex flex-wrap gap-2 align-items-center">
          @if($records->isEmpty())
          <form action="{{ route('admin.moderation-duties.import-marks') }}" method="POST" class="d-inline">
            @csrf
            <input type="hidden" name="exam_session_id" value="{{ $selectedSession }}">
            <input type="hidden" name="erp_subject_id" value="{{ $selectedSubject }}">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-download me-2"></i>Import Evaluator Marks
            </button>
          </form>
          <span class="text-muted">No moderation records found. Import evaluator marks to start.</span>
          @else
          <form action="{{ route('admin.moderation-duties.import-marks') }}" method="POST" class="d-inline">
            @csrf
            <input type="hidden" name="exam_session_id" value="{{ $selectedSession }}">
            <input type="hidden" name="erp_subject_id" value="{{ $selectedSubject }}">
            <button type="submit" class="btn btn-outline-primary" onclick="return confirm('This will re-import evaluator marks. Existing moderator marks will be preserved. Continue?')">
              <i class="fas fa-sync me-2"></i>Re-Import Marks
            </button>
          </form>
          <form action="{{ route('admin.moderation-duties.bulk-adjust') }}" method="POST" class="d-inline">
            @csrf
            <input type="hidden" name="exam_session_id" value="{{ $selectedSession }}">
            <input type="hidden" name="erp_subject_id" value="{{ $selectedSubject }}">
            <button type="submit" class="btn btn-warning" onclick="return confirm('Auto-adjust all records? Records exceeding threshold will be set to average of evaluator and moderator marks.')">
              <i class="fas fa-magic me-2"></i>Bulk Adjust (Threshold: {{ $threshold }})
            </button>
          </form>
          <form action="{{ route('admin.moderation-duties.finalize') }}" method="POST" class="d-inline">
            @csrf
            <input type="hidden" name="exam_session_id" value="{{ $selectedSession }}">
            <input type="hidden" name="erp_subject_id" value="{{ $selectedSubject }}">
            <button type="submit" class="btn btn-success" onclick="return confirm('Finalize all adjusted records? This action cannot be undone.')">
              <i class="fas fa-lock me-2"></i>Finalize Moderation
            </button>
          </form>
          <div class="ms-auto">
            <span class="badge bg-light text-dark border">
              <i class="fas fa-info-circle me-1"></i>Deviation Threshold: <strong>{{ $threshold }}</strong> marks
            </span>
          </div>
          @endif
        </div>
      </div>

      <!-- Legend -->
      @if($records->isNotEmpty())
      <div class="mb-3">
        <span class="me-3"><span class="badge bg-success">&nbsp;</span> Within threshold (&le; {{ $threshold }})</span>
        <span class="me-3"><span class="badge bg-warning">&nbsp;</span> Near threshold ({{ $threshold - 3 }}&ndash;{{ $threshold }})</span>
        <span class="me-3"><span class="badge bg-danger">&nbsp;</span> Exceeds threshold (> {{ $threshold }})</span>
        <span class="me-3"><span class="badge bg-secondary">&nbsp;</span> Not yet moderated</span>
      </div>
      @endif

      <!-- Comparison Table -->
      @if($records->isNotEmpty())
      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="compareTable">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">#</th>
                  <th>Student ID</th>
                  <th>Student Name</th>
                  <th class="text-center">
                    <span class="text-primary"><i class="fas fa-pen me-1"></i>Evaluator</span>
                  </th>
                  <th class="text-center">
                    <span class="text-info"><i class="fas fa-user-check me-1"></i>Moderator</span>
                  </th>
                  <th class="text-center">Difference</th>
                  <th class="text-center">
                    <span class="text-success"><i class="fas fa-check-circle me-1"></i>Adjusted</span>
                  </th>
                  <th class="text-center">Status</th>
                  <th class="text-end pe-4">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($records as $index => $record)
                <tr id="row-{{ $record->id }}" class="{{ $record->difference !== null && $record->difference > $threshold ? 'table-danger' : '' }}">
                  <td class="ps-4">{{ $index + 1 }}</td>
                  <td><span class="fw-semibold font-monospace">{{ $record->erp_student_id }}</span></td>
                  <td>{{ $record->student->name ?? $record->student->FIRST_NAME ?? 'N/A' }}</td>
                  <td class="text-center">
                    <span class="badge bg-primary fs-6 px-3">{{ number_format($record->evaluator_marks, 2) }}</span>
                  </td>
                  <td class="text-center">
                    @if($record->moderator_marks !== null)
                    <span class="badge bg-info fs-6 px-3">{{ number_format($record->moderator_marks, 2) }}</span>
                    @else
                    <div class="input-group input-group-sm" style="max-width: 140px; margin: 0 auto;">
                      <input type="number" step="0.01" min="0" class="form-control mod-marks-input" data-id="{{ $record->id }}" placeholder="Enter">
                      <button class="btn btn-info btn-save-mod" data-id="{{ $record->id }}" title="Save">
                        <i class="fas fa-check"></i>
                      </button>
                    </div>
                    @endif
                  </td>
                  <td class="text-center" id="diff-{{ $record->id }}">
                    @if($record->difference !== null)
                    @if($record->difference > $threshold)
                    <span class="badge bg-danger fs-6 px-3" title="Exceeds threshold">
                      <i class="fas fa-exclamation-triangle me-1"></i>{{ number_format($record->difference, 2) }}
                    </span>
                    @elseif($record->difference > ($threshold - 3))
                    <span class="badge bg-warning text-dark fs-6 px-3" title="Near threshold">
                      <i class="fas fa-exclamation-circle me-1"></i>{{ number_format($record->difference, 2) }}
                    </span>
                    @else
                    <span class="badge bg-success fs-6 px-3" title="Within threshold">
                      {{ number_format($record->difference, 2) }}
                    </span>
                    @endif
                    @else
                    <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td class="text-center" id="adj-{{ $record->id }}">
                    @if($record->adjusted_marks !== null)
                    <span class="badge bg-success fs-6 px-3">{{ number_format($record->adjusted_marks, 2) }}</span>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td class="text-center" id="status-{{ $record->id }}">
                    @if($record->status == 'finalized')
                    <span class="badge bg-dark">Finalized</span>
                    @elseif($record->status == 'adjusted')
                    <span class="badge bg-success">Adjusted</span>
                    @elseif($record->status == 'moderated')
                    <span class="badge bg-info">Moderated</span>
                    @else
                    <span class="badge bg-secondary">Pending</span>
                    @endif
                  </td>
                  <td class="text-end pe-4">
                    @if($record->status != 'finalized')
                    <button class="btn btn-sm btn-outline-success btn-adjust" data-id="{{ $record->id }}"
                      data-evaluator="{{ $record->evaluator_marks }}"
                      data-moderator="{{ $record->moderator_marks }}"
                      data-adjusted="{{ $record->adjusted_marks }}"
                      data-remarks="{{ $record->remarks }}"
                      title="Adjust Marks" {{ $record->moderator_marks === null ? 'disabled' : '' }}>
                      <i class="fas fa-sliders-h"></i>
                    </button>
                    @else
                    <i class="fas fa-lock text-muted" title="Finalized"></i>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      @endif
      @endif
    </div>
  </main>
  <!--end main wrapper-->
</div>

<!-- Adjust Modal -->
<div class="modal fade" id="adjustModal" tabindex="-1" aria-labelledby="adjustModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="adjustModalLabel"><i class="fas fa-sliders-h me-2"></i>Adjust Marks</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-3">
          <div class="col-6">
            <label class="form-label text-muted small">Evaluator Marks</label>
            <div class="fs-4 fw-bold text-primary" id="modalEvaluator">—</div>
          </div>
          <div class="col-6">
            <label class="form-label text-muted small">Moderator Marks</label>
            <div class="fs-4 fw-bold text-info" id="modalModerator">—</div>
          </div>
        </div>
        <div class="mb-3 p-3 bg-light rounded">
          <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted">Difference:</span>
            <span class="fs-5 fw-bold" id="modalDifference">—</span>
          </div>
          <div class="d-flex justify-content-between align-items-center mt-1">
            <span class="text-muted">Average:</span>
            <span class="fs-5 fw-bold text-success" id="modalAverage">—</span>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Adjusted Marks <span class="text-danger">*</span></label>
          <input type="number" step="0.01" min="0" class="form-control form-control-lg" id="adjustedMarksInput">
          <div class="form-text">
            <button type="button" class="btn btn-sm btn-outline-success me-1" id="btnUseAvg">Use Average</button>
            <button type="button" class="btn btn-sm btn-outline-primary me-1" id="btnUseEval">Use Evaluator</button>
            <button type="button" class="btn btn-sm btn-outline-info" id="btnUseMod">Use Moderator</button>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Remarks</label>
          <textarea class="form-control" id="adjustRemarksInput" rows="2" maxlength="500" placeholder="Optional remarks"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" id="btnSaveAdjust">
          <i class="fas fa-save me-2"></i>Save Adjustment
        </button>
      </div>
    </div>
  </div>
</div>

@include('includes.footer')

<input type="hidden" id="jsModeratorMarksUrl" value="{{ route('admin.moderation-duties.moderator-marks', '__ID__') }}">
<input type="hidden" id="jsAdjustUrl" value="{{ route('admin.moderation-duties.adjust', '__ID__') }}">
<input type="hidden" id="jsThreshold" value="{{ $threshold }}">
<input type="hidden" id="jsCsrfToken" value="{{ csrf_token() }}">

<style>
  .table-danger {
    background-color: rgba(220, 53, 69, 0.08) !important;
  }

  .table-danger:hover {
    background-color: rgba(220, 53, 69, 0.15) !important;
  }

  .mod-marks-input {
    text-align: center;
  }

  .icon-wrapper {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var csrfToken = document.getElementById('jsCsrfToken').value;
    var modUrl = document.getElementById('jsModeratorMarksUrl').value;
    var adjUrl = document.getElementById('jsAdjustUrl').value;
    var threshold = parseFloat(document.getElementById('jsThreshold').value);
    var currentAdjustId = null;

    // Save moderator marks inline
    document.querySelectorAll('.btn-save-mod').forEach(function(btn) {
      btn.addEventListener('click', function() {
        var id = this.getAttribute('data-id');
        var input = document.querySelector('.mod-marks-input[data-id="' + id + '"]');
        var marks = input.value;
        if (!marks || isNaN(marks) || parseFloat(marks) < 0) {
          input.classList.add('is-invalid');
          return;
        }
        input.classList.remove('is-invalid');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch(modUrl.replace('__ID__', id), {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json'
            },
            body: JSON.stringify({
              moderator_marks: marks
            })
          })
          .then(function(r) {
            return r.json();
          })
          .then(function(data) {
            if (data.success) {
              var td = input.closest('td');
              td.innerHTML = '<span class="badge bg-info fs-6 px-3">' + parseFloat(marks).toFixed(2) + '</span>';

              var diffCell = document.getElementById('diff-' + id);
              var diff = parseFloat(data.difference);
              var badgeClass = diff > threshold ? 'bg-danger' : (diff > (threshold - 3) ? 'bg-warning text-dark' : 'bg-success');
              var icon = diff > threshold ? '<i class="fas fa-exclamation-triangle me-1"></i>' : (diff > (threshold - 3) ? '<i class="fas fa-exclamation-circle me-1"></i>' : '');
              diffCell.innerHTML = '<span class="badge ' + badgeClass + ' fs-6 px-3">' + icon + diff.toFixed(2) + '</span>';

              var statusCell = document.getElementById('status-' + id);
              statusCell.innerHTML = '<span class="badge bg-info">Moderated</span>';

              var row = document.getElementById('row-' + id);
              if (data.flagged) {
                row.classList.add('table-danger');
              }

              var adjustBtn = row.querySelector('.btn-adjust');
              if (adjustBtn) {
                adjustBtn.disabled = false;
                adjustBtn.setAttribute('data-moderator', marks);
              }
            }
          })
          .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i>';
            alert('Error saving moderator marks');
          });
      });
    });

    // Open adjust modal
    document.querySelectorAll('.btn-adjust').forEach(function(btn) {
      btn.addEventListener('click', function() {
        currentAdjustId = this.getAttribute('data-id');
        var evaluator = parseFloat(this.getAttribute('data-evaluator'));
        var moderator = parseFloat(this.getAttribute('data-moderator'));
        var adjusted = this.getAttribute('data-adjusted');
        var remarks = this.getAttribute('data-remarks') || '';

        document.getElementById('modalEvaluator').textContent = evaluator.toFixed(2);
        document.getElementById('modalModerator').textContent = moderator.toFixed(2);

        var diff = Math.abs(evaluator - moderator);
        var diffEl = document.getElementById('modalDifference');
        diffEl.textContent = diff.toFixed(2);
        diffEl.className = 'fs-5 fw-bold ' + (diff > threshold ? 'text-danger' : 'text-success');

        var avg = ((evaluator + moderator) / 2).toFixed(2);
        document.getElementById('modalAverage').textContent = avg;

        var adjInput = document.getElementById('adjustedMarksInput');
        adjInput.value = adjusted && adjusted !== 'null' ? parseFloat(adjusted).toFixed(2) : avg;
        document.getElementById('adjustRemarksInput').value = remarks;

        document.getElementById('btnUseAvg').onclick = function() {
          adjInput.value = avg;
        };
        document.getElementById('btnUseEval').onclick = function() {
          adjInput.value = evaluator.toFixed(2);
        };
        document.getElementById('btnUseMod').onclick = function() {
          adjInput.value = moderator.toFixed(2);
        };

        var modal = new bootstrap.Modal(document.getElementById('adjustModal'));
        modal.show();
      });
    });

    // Save adjustment
    document.getElementById('btnSaveAdjust').addEventListener('click', function() {
      if (!currentAdjustId) return;
      var marks = document.getElementById('adjustedMarksInput').value;
      var remarks = document.getElementById('adjustRemarksInput').value;
      if (!marks || isNaN(marks) || parseFloat(marks) < 0) return;

      this.disabled = true;
      this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
      var btn = this;

      fetch(adjUrl.replace('__ID__', currentAdjustId), {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            adjusted_marks: marks,
            remarks: remarks
          })
        })
        .then(function(r) {
          return r.json();
        })
        .then(function(data) {
          if (data.success) {
            var adjCell = document.getElementById('adj-' + currentAdjustId);
            adjCell.innerHTML = '<span class="badge bg-success fs-6 px-3">' + data.adjusted_marks + '</span>';

            var statusCell = document.getElementById('status-' + currentAdjustId);
            statusCell.innerHTML = '<span class="badge bg-success">Adjusted</span>';

            var adjustBtn = document.querySelector('.btn-adjust[data-id="' + currentAdjustId + '"]');
            if (adjustBtn) {
              adjustBtn.setAttribute('data-adjusted', marks);
              adjustBtn.setAttribute('data-remarks', remarks);
            }

            bootstrap.Modal.getInstance(document.getElementById('adjustModal')).hide();
          }
          btn.disabled = false;
          btn.innerHTML = '<i class="fas fa-save me-2"></i>Save Adjustment';
        })
        .catch(function() {
          btn.disabled = false;
          btn.innerHTML = '<i class="fas fa-save me-2"></i>Save Adjustment';
          alert('Error saving adjustment');
        });
    });
  });
</script>