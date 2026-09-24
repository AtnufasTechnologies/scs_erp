@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Exam Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.exams.index', ['module' => ($module ?? 'SA')]) }}">Exams</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create Exam</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid py-4">
      @php
      $activeModule = $module ?? 'SA';
      @endphp


      @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="fa fa-exclamation-triangle me-2"></i>Validation Errors:</strong>
        <ul class="mb-0 mt-2">
          @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <!-- Create Form -->
      <div class="row">
        <div class="col-lg-10 col-md-12 mx-auto">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent border-bottom py-3">
              <h6 class="mb-0 fw-bold"><i class="fas fa-edit me-2 text-primary"></i>Exam Information</h6>
            </div>
            <div class="card-body p-4">
              <form action="{{ route('coe.exams.store') }}" method="POST" id="examForm">
                @csrf
                <input type="hidden" name="assessment_type" value="{{ old('assessment_type', $activeModule) }}">
                <input type="hidden" name="module" value="{{ $activeModule }}">

                <!-- Basic Information -->
                <div class="row mb-4">
                  <div class="col-12">
                    <h6 class="text-primary fw-bold mb-3">Basic Information</h6>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="examName" class="form-label fw-bold">
                      Exam Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="examName" name="name"
                      placeholder="e.g., End Semester Examination - May 2026"
                      value="{{ old('name') }}" required>
                    <small class="text-muted">Enter the full name of the examination</small>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label for="examType" class="form-label fw-bold">
                      Exam Type <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="examType" name="exam_type" required>
                      <option value="" selected disabled>Select exam type...</option>
                      <option value="Regular" {{ old('exam_type') == 'Regular' ? 'selected' : '' }}>Regular</option>
                      <option value="Backlog" {{ old('exam_type') == 'Backlog' ? 'selected' : '' }}>Backlog</option>
                      <option value="Improvement" {{ old('exam_type') == 'Improvement' ? 'selected' : '' }}>Improvement</option>
                      <option value="Special" {{ old('exam_type') == 'Special' ? 'selected' : '' }}>Special</option>
                    </select>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label for="semester" class="form-label fw-bold">
                      Semester <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="semester" name="semester" required>
                      <option value="" selected disabled>Select semester...</option>
                      <option value="Odd" {{ old('semester') == 'Odd' ? 'selected' : '' }}>Odd Semester</option>
                      <option value="Even" {{ old('semester') == 'Even' ? 'selected' : '' }}>Even Semester</option>
                    </select>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label for="selectedBatchIds" class="form-label fw-bold">
                      Applicable Batches <span class="text-danger">*</span>
                    </label>
                    <select class="select-multiple" id="selectedBatchIds" name="selected_batch_ids[]" multiple>
                      @foreach(($batches ?? collect()) as $batch)
                      <option value="{{ (int) $batch->id }}" {{ collect(old('selected_batch_ids', []))->map(fn($id) => (int) $id)->contains((int) $batch->id) ? 'selected' : '' }}>
                        {{ $batch->batch_name }}
                      </option>
                      @endforeach
                    </select>
                    <small class="text-muted" id="selectedBatchHelpText">First select semester scope (Odd/Even), then choose one or more batches.</small>
                  </div>

                  <div class="col-12 mb-3">
                    <div class="border rounded p-3 bg-light-subtle">
                      <label class="form-label fw-bold mb-2">Semesters As Per Applicable Batches</label>
                      <div id="applicableBatchSemestersPreview" class="text-muted small mb-2">Select one or more batches to view available semesters.</div>
                      <div id="involvedSemestersPreview" class="text-muted small">Select semester config (Odd/Even) to preview involved semesters.</div>
                    </div>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label for="programType" class="form-label fw-bold">
                      Program Type <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="programType" name="program_type" required>
                      <option value="" selected disabled>Select program type...</option>
                      <option value="UG" {{ old('program_type') == 'UG' ? 'selected' : '' }}>UG</option>
                      <option value="PG" {{ old('program_type') == 'PG' ? 'selected' : '' }}>PG</option>
                    </select>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label for="registrationMode" class="form-label fw-bold">
                      Registration Mode <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="registrationMode" name="registration_mode" required>
                      <option value="registration_required" {{ old('registration_mode', 'registration_required') == 'registration_required' ? 'selected' : '' }}>Student Registration Required</option>
                      <option value="auto_registered" {{ old('registration_mode') == 'auto_registered' ? 'selected' : '' }}>Auto Registered</option>
                    </select>
                    <small class="text-muted">Choose whether students must register manually or are auto-registered from ERP.</small>
                  </div>



                </div>

                <hr>

                <!-- Schedule Information -->
                <div class="row mb-4">
                  <div class="col-12">
                    <h6 class="text-primary fw-bold mb-3">Schedule</h6>
                  </div>

                  <div class="col-md-4 mb-3">
                    <label for="startDate" class="form-label fw-bold">
                      Start Date <span class="text-danger">*</span>
                    </label>
                    <input type="date" class="form-control" id="startDate" name="start_date"
                      value="{{ old('start_date') }}" required>
                  </div>

                  <div class="col-md-4 mb-3">
                    <label for="endDate" class="form-label fw-bold">
                      End Date <span class="text-danger">*</span>
                    </label>
                    <input type="date" class="form-control" id="endDate" name="end_date"
                      value="{{ old('end_date') }}" required>
                  </div>

                  <div class="col-md-4 mb-3">
                    <label for="status" class="form-label fw-bold">
                      Exam Status <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="status" name="status" required>
                      <option value="upcoming" {{ old('status', 'upcoming') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                      <option value="ongoing" {{ old('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                      <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                      <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                  </div>
                </div>




                <!-- Submit Buttons -->
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                  <a href="{{ route('coe.exams.index', ['module' => $activeModule]) }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left me-1"></i>Cancel
                  </a>
                  <button type="submit" class="btn btn-success" id="submitBtn">
                    <span id="submitBtnText"><i class="fa fa-save me-2"></i>Create Exam</span>
                    <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<style>
  .gradient-coe {
    background: linear-gradient(135deg, #95a9ff 0%, #782df9 100%);
  }

  .form-label {
    margin-bottom: 0.5rem;
    color: #495057;
  }

  .text-white-80 {
    color: rgba(255, 255, 255, 0.88);
  }

  .form-control:focus,
  .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
  }

  .card {
    transition: all 0.3s ease;
  }
</style>

<script id="batchSemesterMapJson" type="application/json">
  @json($batchSemesterMap ?? [])
</script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('examForm');
    const examName = document.getElementById('examName');
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    const moduleInput = document.querySelector('input[name="module"]');
    const moduleValue = moduleInput ? moduleInput.value : 'SA';
    const semesterSelect = document.getElementById('semester');
    const batchSelect = document.getElementById('selectedBatchIds');
    const selectedBatchHelpText = document.getElementById('selectedBatchHelpText');
    const applicableBatchSemestersPreview = document.getElementById('applicableBatchSemestersPreview');
    const involvedSemestersPreview = document.getElementById('involvedSemestersPreview');
    const batchSemesterMapEl = document.getElementById('batchSemesterMapJson');
    const batchSemesterMap = batchSemesterMapEl ? JSON.parse(batchSemesterMapEl.textContent || '{}') : {};

    const stripMonthYearSuffix = function(value) {
      return value.replace(/\s*-\s*[A-Za-z]+\s+\d{4}$/, '').trim();
    };

    const updateExamNameFromStartDate = function() {
      if (!startDate.value) {
        return;
      }

      const dateObj = new Date(startDate.value + 'T00:00:00');
      const monthYear = dateObj.toLocaleString('en-US', {
        month: 'long',
        year: 'numeric'
      });

      const fallbackBase = moduleValue === 'FA2' ? 'FA-2 Examination' : 'SA Examination';
      const currentBase = stripMonthYearSuffix(examName.value || '') || fallbackBase;
      examName.value = currentBase + ' - ' + monthYear;
    };

    const getSelectedBatchIds = function() {
      if (!batchSelect) {
        return [];
      }

      // Prefer plugin state when available (e.g., Tom Select).
      if (batchSelect.tomselect && typeof batchSelect.tomselect.getValue === 'function') {
        const pluginValue = batchSelect.tomselect.getValue();
        const rawValues = Array.isArray(pluginValue) ? pluginValue : String(pluginValue || '').split(',');

        return rawValues
          .map(function(value) {
            return parseInt(String(value).trim(), 10);
          })
          .filter(function(id) {
            return Number.isFinite(id) && id > 0;
          });
      }

      // Select2-like fallback via jQuery value if plugin wraps the native select.
      if (window.jQuery && typeof window.jQuery === 'function') {
        const jqValues = window.jQuery(batchSelect).val();
        if (Array.isArray(jqValues)) {
          return jqValues
            .map(function(value) {
              return parseInt(String(value).trim(), 10);
            })
            .filter(function(id) {
              return Number.isFinite(id) && id > 0;
            });
        }
      }

      return Array.from(batchSelect.selectedOptions || [])
        .map(function(option) {
          return parseInt(option.value, 10);
        })
        .filter(function(id) {
          return Number.isFinite(id) && id > 0;
        });
    };

    const renderInvolvedSemesters = function() {
      if (!involvedSemestersPreview || !applicableBatchSemestersPreview) {
        return;
      }

      const selectedSemesterScope = (semesterSelect && semesterSelect.value ? semesterSelect.value : '').toUpperCase();
      const hasValidScope = selectedSemesterScope === 'ODD' || selectedSemesterScope === 'EVEN';

      if (selectedBatchHelpText) {
        selectedBatchHelpText.textContent = hasValidScope ?
          'Select one or more batches. Preview will show only semesters involved for ' + selectedSemesterScope + ' scope.' :
          'First select semester scope (Odd/Even), then choose one or more batches.';
      }

      if (!hasValidScope) {
        applicableBatchSemestersPreview.className = 'text-muted small mb-2';
        applicableBatchSemestersPreview.textContent = 'Select semester scope first, then choose applicable batches.';
        involvedSemestersPreview.className = 'text-muted small';
        involvedSemestersPreview.textContent = 'Involved semester preview will appear after selecting Odd/Even and batches.';
        return;
      }

      const selectedBatchIds = getSelectedBatchIds();

      const availableSemesterById = {};
      selectedBatchIds.forEach(function(batchId) {
        const semesterList = batchSemesterMap[String(batchId)] || batchSemesterMap[batchId] || [];
        semesterList.forEach(function(semesterItem) {
          const semesterId = parseInt(semesterItem.id, 10);
          if (!Number.isFinite(semesterId) || semesterId <= 0) {
            return;
          }

          if (!availableSemesterById[semesterId]) {
            availableSemesterById[semesterId] = {
              id: semesterId,
              title: String(semesterItem.title || ('Semester ' + semesterId)),
              isOdd: !!semesterItem.is_odd,
            };
          }
        });
      });

      const availableSemesters = Object.values(availableSemesterById).sort(function(a, b) {
        return a.id - b.id;
      });

      if (selectedBatchIds.length === 0) {
        applicableBatchSemestersPreview.className = 'text-muted small mb-2';
        applicableBatchSemestersPreview.textContent = 'Select one or more batches to view available semesters for ' + selectedSemesterScope + ' scope.';
      } else if (availableSemesters.length === 0) {
        applicableBatchSemestersPreview.className = 'text-warning small mb-2';
        applicableBatchSemestersPreview.textContent = 'No semester data found for selected batches.';
      } else {
        applicableBatchSemestersPreview.className = 'small mb-2';
        applicableBatchSemestersPreview.textContent = '';
      }

      if (selectedBatchIds.length === 0) {
        involvedSemestersPreview.className = 'text-muted small';
        involvedSemestersPreview.textContent = 'Select batches to preview involved semesters.';
        return;
      }

      const semesterById = {};
      availableSemesters.forEach(function(semesterItem) {
        const isOdd = !!semesterItem.isOdd;
        if ((selectedSemesterScope === 'ODD' && !isOdd) || (selectedSemesterScope === 'EVEN' && isOdd)) {
          return;
        }

        const semesterId = parseInt(semesterItem.id, 10);
        if (!Number.isFinite(semesterId) || semesterId <= 0) {
          return;
        }

        if (!semesterById[semesterId]) {
          semesterById[semesterId] = {
            id: semesterId,
            title: String(semesterItem.title || ('Semester ' + semesterId)),
          };
        }
      });

      let resolvedSemesters = Object.values(semesterById).sort(function(a, b) {
        return a.id - b.id;
      });

      if (selectedSemesterScope === 'ODD') {
        resolvedSemesters = resolvedSemesters.slice(0, 4);
      }

      if (resolvedSemesters.length === 0) {
        involvedSemestersPreview.className = 'text-warning small';
        involvedSemestersPreview.textContent = 'No semesters found for selected batches under ' + selectedSemesterScope + ' scope.';
        return;
      }

      const badges = resolvedSemesters.map(function(item) {
        return '<span class="badge bg-secondary-subtle text-dark border me-1 mb-1">' + item.title + '</span>';
      }).join('');

      involvedSemestersPreview.className = 'small';
      involvedSemestersPreview.innerHTML =
        '<div class="mb-1"><strong>' + resolvedSemesters.length + '</strong> semester(s) included for ' + selectedSemesterScope + ' scope.</div>' +
        '<div>' + badges + '</div>';
    };

    // Date validation
    startDate.addEventListener('change', function() {
      endDate.min = this.value;
      if (endDate.value && endDate.value < this.value) {
        endDate.value = this.value;
      }

      updateExamNameFromStartDate();
    });

    endDate.addEventListener('change', function() {
      startDate.max = this.value;
      if (startDate.value && startDate.value > this.value) {
        alert('End date cannot be before start date');
        this.value = '';
      }
    });

    if (semesterSelect) {
      semesterSelect.addEventListener('change', renderInvolvedSemesters);
    }

    if (batchSelect) {
      batchSelect.addEventListener('change', renderInvolvedSemesters);
      batchSelect.addEventListener('input', renderInvolvedSemesters);

      if (batchSelect.tomselect && typeof batchSelect.tomselect.on === 'function') {
        batchSelect.tomselect.on('change', renderInvolvedSemesters);
        batchSelect.tomselect.on('item_add', renderInvolvedSemesters);
        batchSelect.tomselect.on('item_remove', renderInvolvedSemesters);
      }

      if (window.jQuery && typeof window.jQuery === 'function') {
        window.jQuery(batchSelect).on('change', renderInvolvedSemesters);
      }
    }

    // Form submission
    form.addEventListener('submit', function(e) {
      const submitBtn = document.getElementById('submitBtn');
      const submitBtnText = document.getElementById('submitBtnText');
      const loader = document.getElementById('loader');

      // Disable button and show loader
      submitBtnText.classList.add('d-none');
      loader.classList.remove('d-none');
      submitBtn.disabled = true;
    });

    if (startDate.value) {
      updateExamNameFromStartDate();
    }

    renderInvolvedSemesters();
    setTimeout(renderInvolvedSemesters, 200);
  });
</script>

@include('includes.footer')