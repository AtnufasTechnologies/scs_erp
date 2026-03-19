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
          <strong>{{ $weekStart->format('M d, Y') }} - {{ $weekEnd->format('M d, Y') }}</strong>
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
                  data-class-type="{{ $entry->class_type }}">
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
            <label for="entry_class_type" class="form-label">Type of Class</label>
            <select class="form-select" id="entry_class_type" name="class_type">
              <option value="">Select class type...</option>
              <option value="regular">Regular</option>
              <option value="extra">Extra</option>
              <option value="substitution">Substitution</option>
            </select>
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
</style>

@include('includes.footer')

<script>
  $(document).ready(function() {
    let currentEntryId = null;
    const modal = new bootstrap.Modal(document.getElementById('entryModal'));

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
      $('#entry_datetime').val(formattedDate + ' - Period ' + hour);

      // If editing, load existing data
      if (entryId) {
        $('#entry_description').val($existingBlock.data('description') || '');
        $('#entry_methodology').val($existingBlock.data('methodology') || '');
        $('#entry_class_type').val($existingBlock.data('class-type') || '');
        $('#entryModalTitle').text('Edit Work Entry');
      } else {
        $('#entryModalTitle').text('Add Work Entry');
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

      if (!description) {
        alert('Please enter a work description');
        return;
      }

      $btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Saving...');

      const url = entryId ?
        '{{ route("faculty.workdiary.update", ":id") }}'.replace(':id', entryId) :
        '{{ route("faculty.workdiary.store") }}';

      const method = entryId ? 'PUT' : 'POST';

      $.ajax({
        url: url,
        type: method,
        data: {
          date: date,
          hour: hour,
          description: description,
          methodology: methodology,
          class_type: classType,
          _token: '{{ csrf_token() }}'
        },
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
  });
</script>