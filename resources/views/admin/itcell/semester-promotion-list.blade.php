@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid p-4">
  <h4>Generated Student List / For <span class="text-primary">Semester</span> Promotion</h4>

  <div class="container-fluid py-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <form action="{{route('bulk.semester.promotion')}}" method="POST" id="semesterPromotionForm">
          @csrf

          <input type="hidden" name="batch" value="{{ $batch }}">
          <input type="hidden" name="campus" value="{{ $campus }}">

          <div class="alert alert-info d-flex align-items-center">
            <i class="fa fa-info-circle fs-4 me-3"></i>
            <div>
              <strong>Quick Semester Promotion:</strong> All students are marked <strong class="badge badge-success">Promote</strong> by default.
              Only check the box for students who should <strong class="badge badge-danger">Not Promote</strong>.
            </div>
          </div>

          @if($students->isEmpty())
          <div class="alert alert-warning">
            <i class="fa fa-exclamation-triangle me-2"></i>No students found for selected batch and campus.
          </div>
          @else
          <div class="row mb-3">
            <div class="col-md-6">
              <div class="input-group">
                <span class="input-group-text bg-white">
                  <i class="fa fa-search"></i>
                </span>
                <input type="text" class="form-control" id="studentSearch"
                  placeholder="Search by name or roll number..."
                  autocomplete="off">
                <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                  <i class="fa fa-times"></i> Clear
                </button>
              </div>
              <small class="text-muted">
                <span id="searchResultCount"></span>
              </small>
            </div>
            <div class="col-md-6 text-end d-flex align-items-center justify-content-end">
              <span class="badge bg-primary fs-6 px-3 py-2">
                Total Students: <strong id="visibleCount">{{ $students->count() }}</strong>
              </span>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-md-12">
              <div class="alert alert-light border">
                <strong>Summary:</strong>
                <span class="ms-2">Total: <strong id="totalCount">{{ $students->count() }}</strong></span>
                <span class="ms-3 text-success">Promote: <strong id="presentCount">{{ $students->count() }}</strong></span>
                <span class="ms-3 text-danger">Dont Promote: <strong id="absentCount">0</strong></span>
                <button type="submit" class="btn btn-success mx-5" id="submitBtn">
                  <span id="submitBtnText"><i class="fa fa-save me-1"></i> Promote Semester</span>
                  <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
              </div>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover">
              <thead class="table-light sticky-top">
                <tr>
                  <th width="5%">#</th>
                  <th width="10%">Batch</th>
                  <th width="10%">Current Semester</th>
                  <th width="10%">Next Semester</th>
                  <th width="15%">Roll No</th>
                  <th width="30%">Student Name</th>
                  <th width="10%" class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($students as $index => $student)
                @php
                $currentSemester = (int)($student->activeSemesterConfig->semester_id ?? 1);
                @endphp
                <tr class="student-row table-success" data-student-id="{{ $student->id }}">
                  <td>{{ $index + 1 }}</td>
                  <td>{{ $student->batchmaster->batch_name ?? 'N/A' }}</td>
                  <td>{{ $currentSemester }}</td>
                  <td>{{ $currentSemester + 1 }}</td>
                  <td><span class="badge bg-secondary text-uppercase">{{ $student->roll_no ?? 'N/A' }}</span></td>
                  <td class="student-name">{{ $student->first_name }} {{ $student->last_name }}</td>
                  <td class="text-center">
                    <div class="form-check d-inline-block">
                      <input class="form-check-input status-checkbox" type="checkbox"
                        value="absent"
                        id="absent_{{ $student->id }}"
                        data-student="{{ $student->id }}">
                      <label class="form-check-label" for="absent_{{ $student->id }}"></label>
                    </div>
                  </td>
                  <input type="hidden" name="student[{{ $student->id }}]" id="status_{{ $student->id }}" value="present">
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @endif
        </form>
      </div>
    </div>
  </div>
</div>

@include('includes.footer')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.status-checkbox').forEach(checkbox => {
      checkbox.addEventListener('change', function() {
        const studentId = this.dataset.student;
        const row = this.closest('tr');

        if (this.checked) {
          document.getElementById('status_' + studentId).value = 'absent';
          row.classList.remove('table-success');
          row.classList.add('table-danger');
        } else {
          document.getElementById('status_' + studentId).value = 'present';
          row.classList.remove('table-danger');
          row.classList.add('table-success');
        }

        updateSummary();
      });
    });

    function updateSummary() {
      const total = document.querySelectorAll('.student-row').length;
      let absent = 0;

      document.querySelectorAll('[id^="status_"]').forEach(input => {
        if (input.value === 'absent') absent++;
      });

      const present = total - absent;
      document.getElementById('totalCount').textContent = total;
      document.getElementById('presentCount').textContent = present;
      document.getElementById('absentCount').textContent = absent;
    }

    const searchInput = document.getElementById('studentSearch');
    const searchResultCount = document.getElementById('searchResultCount');
    const visibleCount = document.getElementById('visibleCount');

    function performSearch() {
      const searchTerm = searchInput.value.toLowerCase().trim();
      const rows = document.querySelectorAll('.student-row');
      let visibleStudents = 0;

      rows.forEach(row => {
        const rollNo = row.querySelector('td:nth-child(5)').textContent.toLowerCase();
        const studentName = row.querySelector('.student-name').textContent.toLowerCase();

        if (rollNo.includes(searchTerm) || studentName.includes(searchTerm)) {
          row.style.display = '';
          visibleStudents++;
        } else {
          row.style.display = 'none';
        }
      });

      visibleCount.textContent = visibleStudents;

      if (searchTerm) {
        if (visibleStudents === 0) {
          searchResultCount.textContent = 'No students found';
          searchResultCount.classList.add('text-danger');
          searchResultCount.classList.remove('text-success');
        } else {
          searchResultCount.textContent = `Found ${visibleStudents} student${visibleStudents !== 1 ? 's' : ''}`;
          searchResultCount.classList.add('text-success');
          searchResultCount.classList.remove('text-danger');
        }
      } else {
        searchResultCount.textContent = '';
      }
    }

    window.clearSearch = function() {
      searchInput.value = '';
      performSearch();
      searchInput.focus();
    };

    searchInput?.addEventListener('input', performSearch);

    document.getElementById('semesterPromotionForm')?.addEventListener('submit', function() {
      var submitBtn = document.getElementById('submitBtn');
      var submitBtnText = document.getElementById('submitBtnText');
      var loader = document.getElementById('loader');
      if (submitBtn && submitBtnText && loader) {
        submitBtn.disabled = true;
        submitBtnText.classList.add('d-none');
        loader.classList.remove('d-none');
      }
    });

    updateSummary();
  });
</script>