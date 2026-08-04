@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="mb-1">ITCELL Student Campus Transfer</h4>
      <p class="text-muted mb-0">Transfer enrolled students between Sonada and Siliguri without changing roll number, while preserving transfer history.</p>
    </div>
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

  <div class="alert alert-info">
    <strong>Rules enforced:</strong> Roll number is never changed. Only campus and enrolled program are updated, and every transfer is logged.
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form method="GET" action="{{ route('itcell.student-campus-transfer.index') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label">Current Campus</label>
          <select name="campus_id" class="form-select" required>
            @foreach($campuses as $campus)
            <option value="{{ $campus->id }}" {{ (int)$selectedCampusId === (int)$campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-7">
          <label class="form-label">Search Student</label>
          <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Roll no, register no, application code, or name">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">Generate List</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-header bg-transparent">
      <h5 class="mb-0">Eligible Students</h5>
    </div>
    <div class="card-body">
      @if($students->isEmpty())
      <div class="alert alert-warning mb-0">No students found for the selected filters.</div>
      @else
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Roll No</th>
              <th>Student</th>
              <th>Current Campus</th>
              <th>Current Program</th>
              <th>Transfer Action (Campus + Batch + Subject)</th>
            </tr>
          </thead>
          <tbody>
            @foreach($students as $index => $student)
            <tr>
              <td>{{ $students->firstItem() + $index }}</td>
              <td><strong>{{ $student->roll_no ?: 'N/A' }}</strong></td>
              <td>
                {{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) }}
                @if(!empty($student->register_no))
                <div class="small text-muted">Reg: {{ $student->register_no }}</div>
                @endif
              </td>
              <td>{{ $student->campusmaster->name ?? 'N/A' }}</td>
              <td>
                @if($student->stdprogramenrolled)
                {{ $student->stdprogramenrolled->code }} - {{ $student->stdprogramenrolled->name }}
                @else
                <span class="text-muted">Not set</span>
                @endif
              </td>
              <td>
                @php
                $defaultSubjectId = $student->academic_dept_id ?: $student->department;
                @endphp
                <form method="POST" action="{{ route('itcell.student-campus-transfer.store') }}" class="row g-2 transfer-form">
                  @csrf
                  <input type="hidden" name="student_id" value="{{ $student->id }}">
                  <div class="col-12 col-lg-3">
                    <select name="to_campus_id" class="form-select form-select-sm target-campus-select" required>
                      <option value="">Target Campus</option>
                      @foreach($campuses as $campus)
                      @if((int)$campus->id !== (int)$student->campus_id)
                      <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                      @endif
                      @endforeach
                    </select>
                  </div>
                  <div class="col-12 col-lg-2">
                    <select name="target_batch_id" class="form-select form-select-sm target-batch-select" required>
                      <option value="">Batch</option>
                      @foreach($batches as $batch)
                      <option value="{{ $batch->id }}" {{ (int)$student->batch === (int)$batch->id ? 'selected' : '' }}>{{ $batch->batch_name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-12 col-lg-2">
                    <select name="target_subject_id" class="form-select form-select-sm target-subject-select " data-default-subject-id="{{ (int) $defaultSubjectId }}" required>
                      <option value="">Subject (Department)</option>
                    </select>
                  </div>
                  <div class="col-12 col-lg-2">
                    <select name="to_program_id" class="form-select form-select-sm target-program-select" required>
                      <option value="">Target Program</option>
                    </select>
                  </div>
                  <div class="col-12 col-lg-2">
                    <input type="text" name="reason" class="form-control form-control-sm" maxlength="500" placeholder="Reason (optional)">
                  </div>
                  <div class="col-12 col-lg-1 d-grid">
                    <button type="submit" class="btn btn-sm btn-success">Move</button>
                  </div>
                </form>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $students->links('vendor.pagination.bootstrap-5') }}
      </div>
      @endif
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header bg-transparent">
      <h5 class="mb-0">Recent Transfer History</h5>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Student</th>
              <th>Roll No</th>
              <th>From</th>
              <th>To</th>
              <th>Changed By</th>
              <th>Reason</th>
              <th>When</th>
            </tr>
          </thead>
          <tbody>
            @forelse($recentTransfers as $log)
            <tr>
              <td>{{ $log->id }}</td>
              <td>{{ trim(($log->student->first_name ?? '') . ' ' . ($log->student->last_name ?? '')) }}</td>
              <td><strong>{{ $log->roll_no ?: 'N/A' }}</strong></td>
              <td>
                {{ $log->fromCampus->name ?? 'N/A' }}
                <div class="small text-muted">{{ ($log->fromProgram->code ?? '') }} {{ ($log->fromProgram->name ?? '') }}</div>
              </td>
              <td>
                {{ $log->toCampus->name ?? 'N/A' }}
                <div class="small text-muted">{{ ($log->toProgram->code ?? '') }} {{ ($log->toProgram->name ?? '') }}</div>
              </td>
              <td>{{ $log->changedByUser->name ?? 'System' }}</td>
              <td>{{ $log->reason ?: '-' }}</td>
              <td>{{ $log->created_at ? $log->created_at->format('d M Y H:i') : '-' }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-muted">No transfer history found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@php
$subjectsByCampus = $subjects->groupBy('campus_id')->map(function ($items) {
return $items->map(function ($subject) {
return [
'id' => (int) $subject->id,
'name' => (string) ($subject->title ?? $subject->name ?? ''),
];
})->values();
});
@endphp

<script type="application/json" id="subjectsByCampusData">
  @json($subjectsByCampus)
</script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const programUrl = "{{ route('itcell.student-campus-transfer.programs') }}";
    const rawSubjectsMap = document.getElementById('subjectsByCampusData')?.textContent || '{}';
    let subjectsByCampus = {};

    try {
      subjectsByCampus = JSON.parse(rawSubjectsMap);
    } catch (error) {
      subjectsByCampus = {};
    }

    function renderSubjectOptions(campusSelect, subjectSelect) {
      const targetCampusId = String(campusSelect.value || '').trim();
      const options = subjectsByCampus[targetCampusId] || [];
      const defaultSubjectId = String(subjectSelect.dataset.defaultSubjectId || '').trim();

      subjectSelect.innerHTML = '<option value="">Subject</option>';

      options.forEach(function(subject) {
        const option = document.createElement('option');
        option.value = String(subject.id);
        option.textContent = subject.name || 'Subject';
        subjectSelect.appendChild(option);
      });

      if (defaultSubjectId) {
        const hasDefault = options.some(function(subject) {
          return String(subject.id) === defaultSubjectId;
        });
        if (hasDefault) {
          subjectSelect.value = defaultSubjectId;
          return;
        }
      }

      if (options.length === 1) {
        subjectSelect.value = String(options[0].id);
      }
    }

    function renderProgramOptions(programSelect, programs) {
      const list = Array.isArray(programs) ? programs : [];
      programSelect.innerHTML = '<option value="">Target Program</option>';

      list.forEach(function(program) {
        const option = document.createElement('option');
        option.value = String(program.id);
        option.textContent = ((program.code || '') + ' - ' + (program.name || '')).trim();
        programSelect.appendChild(option);
      });

      if (list.length === 1) {
        programSelect.value = String(list[0].id);
      }
    }

    function setMoveButtonState(moveButton, shouldEnable) {
      if (!moveButton) {
        return;
      }
      moveButton.disabled = !shouldEnable;
    }

    async function syncPrograms(form, campusSelect, batchSelect, subjectSelect, programSelect, moveButton) {
      const targetCampusId = String(campusSelect.value || '').trim();
      const targetBatchId = String(batchSelect.value || '').trim();
      const targetSubjectId = String(subjectSelect.value || '').trim();

      renderProgramOptions(programSelect, []);

      if (!targetCampusId || !targetBatchId || !targetSubjectId) {
        setMoveButtonState(moveButton, false);
        return;
      }

      setMoveButtonState(moveButton, false);
      programSelect.innerHTML = '<option value="">Loading programs...</option>';

      try {
        const params = new URLSearchParams({
          campus_id: targetCampusId,
          batch_id: targetBatchId,
          subject_id: targetSubjectId,
        });

        const response = await fetch(programUrl + '?' + params.toString(), {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
          },
        });

        if (!response.ok) {
          throw new Error('Program fetch failed');
        }

        const payload = await response.json();
        const programs = Array.isArray(payload.programs) ? payload.programs : [];
        renderProgramOptions(programSelect, programs);

        if (programs.length === 0) {
          programSelect.innerHTML = '<option value="">No programs available</option>';
          setMoveButtonState(moveButton, false);
          return;
        }

        setMoveButtonState(moveButton, true);
      } catch (error) {
        programSelect.innerHTML = '<option value="">Failed to load programs</option>';
        setMoveButtonState(moveButton, false);
      }
    }

    document.querySelectorAll('.transfer-form').forEach(function(form) {
      const campusSelect = form.querySelector('.target-campus-select');
      const batchSelect = form.querySelector('.target-batch-select');
      const subjectSelect = form.querySelector('.target-subject-select');
      const programSelect = form.querySelector('.target-program-select');
      const moveButton = form.querySelector('button[type="submit"]');

      if (!campusSelect || !batchSelect || !subjectSelect || !programSelect) {
        return;
      }

      const runSync = function() {
        syncPrograms(form, campusSelect, batchSelect, subjectSelect, programSelect, moveButton);
      };

      setMoveButtonState(moveButton, false);
      campusSelect.addEventListener('change', function() {
        renderSubjectOptions(campusSelect, subjectSelect);
        runSync();
      });
      batchSelect.addEventListener('change', runSync);
      subjectSelect.addEventListener('change', runSync);

      renderSubjectOptions(campusSelect, subjectSelect);

      form.addEventListener('submit', function(event) {
        if (!programSelect.value) {
          event.preventDefault();
        }
      });
    });
  });
</script>

@include('includes.footer')