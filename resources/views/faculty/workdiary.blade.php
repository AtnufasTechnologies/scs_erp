@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Faculty</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Work Diary</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid py-4">
      <!-- Analytics Cards Section -->
      <div class="row mb-4">
        <div class="col-md-4">
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bx bx-book-open text-success" style="font-size: 24px;"></i>
                  </div>
                </div>
                <div class="flex-grow-1 ms-3">
                  <h6 class="mb-0 text-muted"> Teaching Classes</h6>
                  <h3 class="mb-0 mt-1 fw-bold">{{ $regularCount ?? 0 }}</h3>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bx bx-time-five text-primary" style="font-size: 24px;"></i>
                  </div>
                </div>
                <div class="flex-grow-1 ms-3">
                  <h6 class="mb-0 text-muted">Extra Classes</h6>
                  <h3 class="mb-0 mt-1 fw-bold">{{ $extraCount ?? 0 }}</h3>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bx bx-refresh text-warning" style="font-size: 24px;"></i>
                  </div>
                </div>
                <div class="flex-grow-1 ms-3">
                  <h6 class="mb-0 text-muted"> Substitution Classes</h6>
                  <h3 class="mb-0 mt-1 fw-bold">{{ $substitutionCount ?? 0 }}</h3>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row mb-4">
        <div class="col-12">
          <h2 class="fw-bold">Work Diary</h2>
          <p class="text-muted">Click on any cell to add or view work entries for that period</p>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <div class="d-flex gap-2 align-items-center flex-wrap">
            <a href="{{ route('faculty.workdiary', ['week_start' => $weekStart->copy()->subWeek()->format('Y-m-d')]) }}"
              class="btn btn-sm btn-outline-primary">
              <i class="fa fa-chevron-left"></i> Previous Week
            </a>
            <a href="{{ route('faculty.workdiary') }}"
              class="btn btn-sm btn-primary">
              <i class="fa fa-calendar"></i> This Week
            </a>
            <a href="{{ route('faculty.workdiary', ['week_start' => $weekStart->copy()->addWeek()->format('Y-m-d')]) }}"
              class="btn btn-sm btn-outline-primary">
              Next Week <i class="fa fa-chevron-right"></i>
            </a>
            <div class="input-group" style="width: 200px;">
              <input type="date"
                id="dateSelector"
                class="form-control form-control-sm"
                value="{{ $weekStart->format('Y-m-d') }}"
                title="Jump to specific date">
              <button class="btn btn-sm btn-outline-secondary"
                type="button"
                id="goToDateBtn">
                <i class="fa fa-search"></i>
              </button>
            </div>
          </div>
        </div>
        <div class="col-md-6 text-end">
          <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#holidayModal">
              <i class="bx bx-calendar-x"></i> Mark Holiday
            </button>
            <a href="{{ route('faculty.workdiary.monthly.report') }}" class="btn btn-sm btn-success">
              <i class="bx bx-file-blank"></i> Monthly Report
            </a>
            <strong>{{ $weekStart->format('M d, Y') }} - {{ $weekEnd->format('M d, Y') }}</strong>
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="calendar-table">
          <thead>
            <tr>
              <th class="hour-col">Period</th>
              @foreach($weekdays as $day)
              <th>{{ $day }}</th>
              @endforeach
            </tr>
          </thead>
          <tbody>
            @foreach($hours as $hourMaster)
            <tr>
              <td class="hour-col">{{ $hourMaster->title }}</td>
              @foreach($weekdays as $day)
              @php
              $dayDate = $weekStart->copy();
              while ($dayDate->format('l') !== $day) {
              $dayDate->addDay();
              }
              $dayEntries = isset($calendar[$day][$hourMaster->title]) ? $calendar[$day][$hourMaster->title] : [];
              @endphp
              <td class="calendar-cell {{ count($dayEntries) > 0 ? 'has-entry' : '' }}"
                data-date="{{ $dayDate->format('Y-m-d') }}"
                data-hour="{{ $hourMaster->title }}"
                data-weekday="{{ $day }}"
                style="cursor: pointer;">
                @if(count($dayEntries) > 0)
                @foreach($dayEntries as $entry)
                <div class="calendar-block"
                  data-entry-id="{{ $entry->id }}"
                  data-description="{{ $entry->description }}"
                  data-methodology="{{ $entry->methodology }}"
                  data-class-type="{{ $entry->class_type }}"
                  data-work-type="{{ $entry->work_type ?? '' }}"
                  data-document-path="{{ $entry->document_path ?? '' }}">
                  @if($entry->class_type)
                  <div><strong>{{ ucfirst($entry->class_type) }}</strong></div>
                  @endif
                  @if($entry->methodology)
                  <div class="course">{{ $entry->methodology }}</div>
                  @endif
                  <div class="semester">{{ Str::limit($entry->description, 50) }}</div>
                  <div class="entry-actions-inline">
                    <button class="btn btn-xs btn-light edit-entry-btn" title="Edit">
                      <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-xs btn-danger delete-entry-btn" title="Delete">
                      <i class="fa fa-trash"></i>
                    </button>
                  </div>
                </div>
                @endforeach
                @else
                <span style="color:#bbb;">—</span>
                @endif
              </td>
              @endforeach
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

  </main>
  <!--end main wrapper-->


</div>

<!-- Add/Edit Entry Modal -->
<div class="modal fade" id="entryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="entryModalTitle">Add Work Entry</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="entryForm">
          <input type="hidden" id="entry_id" name="entry_id">
          <input type="hidden" id="entry_date" name="date">
          <input type="hidden" id="entry_hour" name="hour">

          <div class="mb-3">
            <label class="form-label">Date & Time</label>
            <input type="text" class="form-control" id="entry_datetime" readonly>
          </div>

          <div class="mb-3">
            <label for="entry_class_type" class="form-label">Type of Class <span class="text-danger">*</span></label>
            <select class="form-select" id="entry_class_type" name="class_type" required>
              <option value="">Select class type...</option>
              <option value="regular">Regular</option>
              <option value="extra">Extra</option>
              <option value="substitution">Substitution</option>
            </select>
          </div>

          <div class="mb-3" id="work_type_container" style="display:none;">
            <label for="entry_work_type" class="form-label">Type of Work <span class="text-danger">*</span></label>
            <select class="form-select" id="entry_work_type" name="work_type">
              <option value="">Select work type...</option>
              <option value="library">Library</option>
              <option value="research">Research</option>
              <option value="prep class">Prep Class</option>
            </select>
          </div>

          <div class="mb-3" id="document_container" style="display:none;">
            <label for="entry_document" class="form-label">Related Document</label>
            <input type="file" class="form-control" id="entry_document" name="document"
              accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
            <div class="form-text">Upload supporting document (PDF, DOC, DOCX, JPG, PNG - Max 5MB)</div>
            <div id="current_document_info" style="display:none;" class="mt-2">
              <small class="text-muted">Current document: <span id="current_document_name"></span></small>
            </div>
          </div>

          <div class="mb-3">
            <label for="entry_methodology" class="form-label">Methodology Used</label>
            <select class="form-select" id="entry_methodology" name="methodology">
              <option value="">Select methodology...</option>
              @foreach($methodologies as $methodology)
              <option value="{{ $methodology->name }}">{{ $methodology->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label for="entry_description" class="form-label">Work Description <span class="text-danger">*</span></label>
            <textarea class="form-control" id="entry_description" name="description" rows="5"
              placeholder="Describe the work you did during this time..." required></textarea>
            <div class="form-text">Maximum 1000 characters</div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveEntryBtn">
          <i class="bx bx-save"></i> Save Entry
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Holiday Modal -->
<div class="modal fade" id="holidayModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bx bx-calendar-x me-2"></i>Mark Holiday/Leave</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="holidayForm">
          @csrf
          <div class="mb-3">
            <label for="holiday_type" class="form-label">Type <span class="text-danger">*</span></label>
            <select class="form-select" id="holiday_type" name="type" required>
              <option value="">Select type...</option>
              <option value="holiday">Holiday</option>
              <option value="leave">Leave</option>
              <option value="vacation">Vacation</option>
            </select>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="holiday_start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="holiday_start_date" name="start_date" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="holiday_end_date" class="form-label">End Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="holiday_end_date" name="end_date" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="holiday_reason" class="form-label">Reason</label>
            <textarea class="form-control" id="holiday_reason" name="reason" rows="3"
              placeholder="Enter reason for holiday/leave (optional)" maxlength="500"></textarea>
            <div class="form-text">Maximum 500 characters</div>
          </div>

          <div class="alert alert-info">
            <i class="bx bx-info-circle me-2"></i>
            <small>This will mark the selected date range as holiday. You cannot add work entries for these dates.</small>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-warning" id="saveHolidayBtn">
          <i class="bx bx-save"></i> Mark Holiday
        </button>
      </div>
    </div>
  </div>
</div>

<style>
  .calendar-table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    background: #535353;
  }

  .calendar-table th,
  .calendar-table td {
    text-align: center;
    vertical-align: middle;
    border: 1px solid #e0e0e0;
    padding: 8px;
  }

  .calendar-table th {
    background: #5e6daa;
    font-weight: 600;
    color: #fff;
  }

  .calendar-table .hour-col {
    background: #f0f4ff;
    font-weight: bold;
    color: #17472f;
    width: 70px;
  }

  .calendar-cell {
    min-height: 80px;
    cursor: pointer;
    background-color: #fff;
    transition: background-color 0.2s;
  }

  .calendar-cell:hover {
    background-color: #f0f8ff;
  }

  .calendar-cell.has-entry {
    background-color: #f5f5f5;
  }

  .calendar-block {
    background: linear-gradient(135deg, #5eaaa2 0%, #3b847c 100%);
    color: #fff;
    border-radius: 8px;
    font-size: 0.95em;
    font-weight: 500;
    box-shadow: 0 2px 8px #0001;
    padding: 6px 4px;
    margin: 2px 0;
    position: relative;
  }

  .calendar-block .course {
    font-size: 0.9em;
    font-weight: 400;
    color: #ffe082;
  }

  .calendar-block .semester {
    font-size: 0.85em;
    color: #b2ffef;
  }

  .entry-actions-inline {
    margin-top: 4px;
    display: none;
  }

  .calendar-block:hover .entry-actions-inline {
    display: flex;
    gap: 4px;
    justify-content: center;
  }

  .btn-xs {
    padding: 2px 6px;
    font-size: 0.75rem;
  }

  .input-group {
    max-width: 200px;
  }

  @media (max-width: 768px) {
    .input-group {
      max-width: 100%;
      margin-top: 8px;
    }
  }

  /* Holiday Styles */
  .calendar-cell.holiday-block {
    background: linear-gradient(135deg, #fff3cd 0%, #fff8e1 100%) !important;
    border: 2px dashed #ffc107 !important;
    cursor: not-allowed !important;
    position: relative;
  }

  .calendar-cell.holiday-block:hover {
    background: linear-gradient(135deg, #ffe69c 0%, #fff3cd 100%) !important;
  }

  .holiday-badge {
    position: absolute;
    top: 2px;
    right: 2px;
    font-size: 12px;
    z-index: 10;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }

  .calendar-cell.holiday-block .calendar-block {
    opacity: 0.6;
    pointer-events: none;
  }
</style>

@include('includes.footer')

<script>
  // Pass week start date from PHP to JavaScript
  const currentWeekStart = '{{ $weekStart->format("Y-m-d") }}';

  $(document).ready(function() {
    let currentEntryId = null;
    const modal = new bootstrap.Modal(document.getElementById('entryModal'));

    // Handle class type change to show/hide work type and document fields
    $('#entry_class_type').on('change', function() {
      const classType = $(this).val();
      if (classType === 'extra') {
        $('#work_type_container').show();
        $('#document_container').show();
        $('#entry_work_type').prop('required', true);
      } else {
        $('#work_type_container').hide();
        $('#document_container').hide();
        $('#entry_work_type').prop('required', false);
        $('#entry_work_type').val('');
        $('#entry_document').val('');
        $('#current_document_info').hide();
      }
    });

    // Date selector functionality
    $('#goToDateBtn').on('click', function() {
      const selectedDate = $('#dateSelector').val();
      if (selectedDate) {
        window.location.href = '{{ route("faculty.workdiary") }}?week_start=' + selectedDate;
      }
    });

    // Allow Enter key to submit date
    $('#dateSelector').on('keypress', function(e) {
      if (e.which === 13) {
        $('#goToDateBtn').click();
      }
    });

    // Click on calendar cell to add/edit entry
    $(document).on('click', '.calendar-cell', function(e) {
      // Don't trigger if clicking on action buttons
      if ($(e.target).closest('.entry-actions-inline').length) {
        return;
      }

      const $cell = $(this);

      // Don't open modal if it's a holiday block
      if ($cell.hasClass('holiday-block')) {
        return;
      }

      const date = $cell.data('date');
      const hour = $cell.data('hour');
      const weekday = $cell.data('weekday');

      // Check if there's an existing entry (only handle first one for now)
      const $existingBlock = $cell.find('.calendar-block').first();
      let entryId = null;

      if ($existingBlock.length > 0) {
        entryId = $existingBlock.data('entry-id');
      }

      console.log('Opening modal for:', weekday, 'Period', hour, 'Date:', date);

      // Reset form
      $('#entryForm')[0].reset();
      currentEntryId = entryId || null;

      // Set form values
      $('#entry_id').val(entryId || '');
      $('#entry_date').val(date);
      $('#entry_hour').val(hour);

      // Format display datetime
      const dateObj = new Date(date + 'T12:00:00');
      const formattedDate = dateObj.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
      $('#entry_datetime').val(formattedDate + ' - Hour ' + hour);

      // If editing, load existing data
      if (entryId) {
        $('#entry_description').val($existingBlock.data('description') || '');
        $('#entry_methodology').val($existingBlock.data('methodology') || '');
        $('#entry_class_type').val($existingBlock.data('class-type') || '');
        $('#entry_work_type').val($existingBlock.data('work-type') || '');

        // Handle document path display
        const documentPath = $existingBlock.data('document-path');
        if (documentPath) {
          const fileName = documentPath.split('/').pop();
          $('#current_document_name').text(fileName);
          $('#current_document_info').show();
        } else {
          $('#current_document_info').hide();
        }

        $('#entryModalTitle').text('Edit Work Entry');

        // Trigger change to show/hide conditional fields
        $('#entry_class_type').trigger('change');
      } else {
        $('#entryModalTitle').text('Add Work Entry');
        $('#current_document_info').hide();
        // Hide conditional fields on new entry
        $('#work_type_container').hide();
        $('#document_container').hide();
      }

      modal.show();
    });

    // Handle edit button click
    $(document).on('click', '.edit-entry-btn', function(e) {
      e.stopPropagation();
      $(this).closest('.calendar-cell').trigger('click');
    });

    // Handle delete button click
    $(document).on('click', '.delete-entry-btn', function(e) {
      e.stopPropagation();

      if (!confirm('Are you sure you want to delete this entry?')) {
        return;
      }

      const $block = $(this).closest('.calendar-block');
      const entryId = $block.data('entry-id');

      $.ajax({
        url: '{{ route("faculty.workdiary.destroy", ":id") }}'.replace(':id', entryId),
        type: 'DELETE',
        data: {
          _token: '{{ csrf_token() }}'
        },
        success: function(response) {
          if (response.success) {
            if (typeof toastr !== 'undefined') {
              toastr.success(response.message);
            }
            location.reload();
          }
        },
        error: function(xhr) {
          alert('Error: Failed to delete entry');
        }
      });
    });

    // Save entry
    $('#saveEntryBtn').on('click', function() {
      const $btn = $(this);
      const entryId = currentEntryId;
      const date = $('#entry_date').val();
      const hour = $('#entry_hour').val();
      const description = $('#entry_description').val().trim();
      const methodology = $('#entry_methodology').val();
      const classType = $('#entry_class_type').val();
      const workType = $('#entry_work_type').val();
      const documentFile = $('#entry_document')[0].files[0];

      if (!description) {
        alert('Please enter a work description');
        return;
      }

      if (!classType) {
        alert('Please select a class type');
        return;
      }

      // Validate work type for extra classes
      if (classType === 'extra' && !workType) {
        alert('Please select type of work for extra classes');
        return;
      }

      $btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Saving...');

      const url = entryId ?
        '{{ route("faculty.workdiary.update", ":id") }}'.replace(':id', entryId) :
        '{{ route("faculty.workdiary.store") }}';

      // Use FormData for file upload
      const formData = new FormData();
      formData.append('date', date);
      formData.append('hour', hour);
      formData.append('description', description);
      formData.append('methodology', methodology || '');
      formData.append('class_type', classType || '');
      formData.append('work_type', workType || '');
      formData.append('_token', '{{ csrf_token() }}');

      if (documentFile) {
        formData.append('document', documentFile);
      }

      // Add _method for PUT requests
      if (entryId) {
        formData.append('_method', 'PUT');
      }

      $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
          if (response.success) {
            if (typeof toastr !== 'undefined') {
              toastr.success(response.message);
            }

            // Reload page to show updated data
            location.reload();
          }
        },
        error: function(xhr) {
          let errorMessage = 'Failed to save entry';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
          }

          if (typeof toastr !== 'undefined') {
            toastr.error(errorMessage);
          } else {
            alert('Error: ' + errorMessage);
          }
        },
        complete: function() {
          $btn.prop('disabled', false).html('<i class="bx bx-save"></i> Save Entry');
        }
      });
    });

    // Holiday Management
    let holidays = [];

    // Debug: Check if elements exist
    console.log('Holiday button exists:', $('#saveHolidayBtn').length);
    console.log('Holiday form exists:', $('#holidayForm').length);
    console.log('Holiday modal exists:', $('#holidayModal').length);

    function loadHolidays() {
      $.ajax({
        url: '{{ route("faculty.workdiary.holidays.get") }}',
        type: 'GET',
        data: {
          month: currentWeekStart
        },
        success: function(response) {
          if (response.success) {
            holidays = response.holidays;
            markHolidaysOnCalendar();
          }
        }
      });
    }

    function markHolidaysOnCalendar() {
      // Remove existing holiday markers
      $('.calendar-cell').removeClass('holiday-block');
      $('.calendar-cell .holiday-badge').remove();

      holidays.forEach(function(holiday) {
        const startDate = new Date(holiday.start_date);
        const endDate = new Date(holiday.end_date);

        let currentDate = new Date(startDate);
        while (currentDate <= endDate) {
          const dateStr = currentDate.toISOString().split('T')[0];
          const $cells = $(`.calendar-cell[data-date="${dateStr}"]`);

          $cells.each(function() {
            const $cell = $(this);
            $cell.addClass('holiday-block');

            // Add holiday badge if not already present
            if ($cell.find('.holiday-badge').length === 0) {
              const typeIcon = holiday.type === 'leave' ? '🏖️' : (holiday.type === 'vacation' ? '✈️' : '🎉');
              const badge = `<div class="holiday-badge" title="${holiday.reason || holiday.type}">${typeIcon}</div>`;
              $cell.prepend(badge);
            }
          });

          currentDate.setDate(currentDate.getDate() + 1);
        }
      });
    }

    // Save Holiday - moved outside the markHolidaysOnCalendar function
    $('#saveHolidayBtn').on('click', function() {
      console.log('Holiday save button clicked');
      const $btn = $(this);
      const $form = $('#holidayForm');

      const startDate = $('#holiday_start_date').val();
      const endDate = $('#holiday_end_date').val();
      const type = $('#holiday_type').val();
      const reason = $('#holiday_reason').val();

      console.log('Form values:', {
        startDate,
        endDate,
        type,
        reason
      });

      if (!startDate || !endDate || !type) {
        alert('Please fill in all required fields');
        return;
      }

      if (new Date(endDate) < new Date(startDate)) {
        alert('End date must be on or after start date');
        return;
      }

      $btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Saving...');

      $.ajax({
        url: '{{ route("faculty.workdiary.holidays.store") }}',
        type: 'POST',
        data: {
          _token: '{{ csrf_token() }}',
          start_date: startDate,
          end_date: endDate,
          type: type,
          reason: reason
        },
        success: function(response) {
          console.log('Success response:', response);
          if (response.success) {
            if (typeof toastr !== 'undefined') {
              toastr.success(response.message);
            } else {
              alert(response.message);
            }
            $('#holidayModal').modal('hide');
            $form[0].reset();
            loadHolidays();
          }
        },
        error: function(xhr) {
          console.log('Error response:', xhr);
          let errorMessage = 'An error occurred while marking holiday';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
          } else if (xhr.responseJSON && xhr.responseJSON.errors) {
            errorMessage = Object.values(xhr.responseJSON.errors).flat().join(', ');
          }
          if (typeof toastr !== 'undefined') {
            toastr.error(errorMessage);
          } else {
            alert('Error: ' + errorMessage);
          }
        },
        complete: function() {
          $btn.prop('disabled', false).html('<i class="bx bx-save"></i> Mark Holiday');
        }
      });
    });

    // Prevent clicking on holiday blocks
    $(document).on('click', '.calendar-cell.holiday-block', function(e) {
      e.stopPropagation();
      const reason = $(this).find('.holiday-badge').attr('title');
      if (typeof toastr !== 'undefined') {
        toastr.info('This day is marked as: ' + reason);
      } else {
        alert('This day is marked as: ' + reason);
      }
      return false;
    });

    // Load holidays when page loads
    loadHolidays();
  });
</script>
</script>