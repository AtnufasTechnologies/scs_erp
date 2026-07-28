@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="mb-1">ITCELL Lateral Entry</h4>
      <p class="text-muted mb-0">Add a student directly to the master, enroll them into a program, generate a roll number, and keep an audit trail.</p>
    </div>
    <a href="{{ route('itcell.lateral-entry.audit') }}" class="btn btn-outline-secondary">View Audit Trail</a>
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
    <div class="card-body">
      <form action="{{ route('itcell.lateral-entry.store') }}" method="POST">
        @csrf
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">First Name *</label>
            <input type="text" name="first_name" class="form-control" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control">
          </div>
          <div class="col-md-2">
            <label class="form-label">Gender *</label>
            <select name="gender" class="form-control" required>
              <option value="1">Male</option>
              <option value="2">Female</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Mobile</label>
            <input type="text" name="mobile_no" class="form-control">
          </div>
          <div class="col-md-2">
            <label class="form-label">Email</label>
            <input type="email" name="mail_id" class="form-control">
          </div>


          <div class="col-md-3">
            <label class="form-label">Campus *</label>
            <select name="campus_id" id="campusSelect" class="form-control" required>
              <option value="">Select campus</option>
              @foreach($campuses as $campus)
              <option value="{{ $campus->id }}">{{ $campus->name }}</option>
              @endforeach
            </select>
          </div>



          <div class="col-md-3">
            <label class="form-label">Program Type*</label>
            <select name="program_type" id="programTypeSelect" class="form-control" required>
              <option value="">Program Type</option>
              @foreach($programstype as $prog)
              <option value="{{ $prog->id }}">{{ $prog->title }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Batch *</label>
            <select name="batch" id="batchSelect" class="form-control" required>
              <option value="">Select batch</option>
              @foreach($batches as $batch)
              <option value="{{ $batch->id }}">{{ $batch->batch_name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Enrolled Program *</label>
            <select name="new_program_id" id="programSelect" class="dselect-example" required>
              <option value="">Select campus and batch first</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Admission Date</label>
            <input type="date" name="admission_date" class="form-control">
          </div>
          <div class="col-md-2">
            <label class="form-label">Current Year</label>
            <input type="number" name="current_year" class="form-control" min="1" max="6" value="2">
          </div>

          <div class="col-md-2">
            <label class="form-label">Semester</label>
            <select name="semester" class="form-select">
              @foreach ($semesters as $sem)
              <option value="{{$sem->id}}">{{$sem->title}}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-7">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" rows="1" class="form-control" placeholder="Why this student is being added via lateral entry"></textarea>
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-success">Create Lateral Entry Student</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="mb-3">Recent Audit Trail</h5>
      <div class="table-responsive">
        <table class="table table-bordered table-sm">
          <thead>
            <tr>
              <th>#</th>
              <th>Student</th>
              <th>Added By</th>
              <th>Type</th>
              <th>Remarks</th>
              <th>Timestamp</th>
            </tr>
          </thead>
          <tbody>
            @forelse($auditLogs as $log)
            <tr>
              <td>{{ $log->id }}</td>
              <td>{{ $log->student->first_name ?? '' }} {{ $log->student->last_name ?? '' }} ({{ $log->student->roll_no ?? 'N/A' }})</td>
              <td>{{ $log->user->name ?? 'System' }}</td>
              <td>{{ ucfirst(str_replace('-', ' ', $log->entry_type)) }}</td>
              <td>{{ $log->remarks }}</td>
              <td>{{ $log->created_at ? $log->created_at->format('d M Y H:i') : '' }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="text-muted">No audit records yet.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@include('includes.footer')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const campusSelect = document.getElementById('campusSelect');
    const batchSelect = document.getElementById('batchSelect');
    const programSelect = document.getElementById('programSelect');
    const programTypeSelect = document.getElementById('programTypeSelect');

    if (!campusSelect || !batchSelect || !programSelect || !programTypeSelect) {
      return;
    }

    const initProgramSearchSelect = () => {
      if (typeof window.dselect !== 'function') {
        return;
      }

      // Avoid duplicate dselect wrappers after AJAX option repaint.
      const nextEl = programSelect.nextElementSibling;
      if (nextEl && nextEl.classList.contains('dselect-wrapper')) {
        nextEl.remove();
      }

      window.dselect(programSelect, {
        search: true,
        creatable: false,
        clearable: true,
        maxHeight: '300px',
        size: 'sm'
      });
    };

    const resetPrograms = () => {
      programSelect.innerHTML = '<option value="">Select campus, program type and batch first</option>';
      initProgramSearchSelect();
    };

    const loadPrograms = async () => {
      const campusId = campusSelect.value;
      const programTypeId = programTypeSelect.value;
      const batchId = batchSelect.value;

      if (!campusId || !programTypeId || !batchId) {
        resetPrograms();
        return;
      }

      programSelect.innerHTML = '<option value="">Loading programs...</option>';

      try {
        const response = await fetch("{{ route('itcell.lateral-entry.programs') }}?campus_id=" + encodeURIComponent(campusId) + "&batch_id=" + encodeURIComponent(batchId) + "&program_type=" + encodeURIComponent(programTypeId), {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        });

        if (!response.ok) {
          throw new Error('Unable to load programs');
        }

        const data = await response.json();
        programSelect.innerHTML = '';

        if (!data.success || !Array.isArray(data.programs) || data.programs.length === 0) {
          programSelect.innerHTML = '<option value="">No enrolled programs found</option>';
          initProgramSearchSelect();
          return;
        }

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'Select program';
        programSelect.appendChild(defaultOption);

        data.programs.forEach(function(program) {
          const option = document.createElement('option');
          option.value = program.id;
          option.textContent = (program.code ? program.code + ' - ' : '') + (program.name || '');
          programSelect.appendChild(option);
        });

        initProgramSearchSelect();
      } catch (error) {
        programSelect.innerHTML = '<option value="">Unable to load programs</option>';
        initProgramSearchSelect();
      }
    };

    campusSelect.addEventListener('change', loadPrograms);
    programTypeSelect.addEventListener('change', loadPrograms);
    batchSelect.addEventListener('change', loadPrograms);
    initProgramSearchSelect();
    loadPrograms();
  });
</script>