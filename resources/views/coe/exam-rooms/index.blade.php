@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Exam Room Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.exams.index') }}">SA Examinations</a></li>
            <li class="breadcrumb-item active" aria-current="page">Exam Rooms</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-3">
      <div class="card gradient-coe shadow-lg border-0 mb-4">
        <div class="card-body p-4">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h4 class="text-dark fw-bold mb-0"><i class="fas fa-door-open me-2"></i>Examination Room Master</h4>
              <p class="text-dark-50 mb-0 mt-1">Manage building, room number, seating layout, capacity and priority for exams</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
              <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                <i class="fa fa-plus-circle me-1"></i>Add Exam Room
              </button>
            </div>
          </div>
        </div>
      </div>

      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      @if ($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa fa-exclamation-triangle me-2"></i>Please fix the highlighted errors and try again.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <div class="card shadow-sm">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover align-middle" id="exportTable">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Building</th>
                  <th>Room Number</th>
                  <th>Layout (R x C)</th>
                  <th>Capacity</th>
                  <th>Priority</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($rooms as $room)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $room->display_building ?? ($room->building ?? 'N/A') }}</td>
                  <td>{{ $room->display_room_number ?? ($room->room_number ?? $room->room_no) }}</td>
                  <td>{{ (int) ($room->display_rows ?? ($room->rows ?? 0)) }} x {{ (int) ($room->display_columns ?? ($room->columns ?? 0)) }}</td>
                  <td>{{ (int) ($room->capacity ?? 0) }}</td>
                  <td>{{ (int) ($room->priority ?? 1) }}</td>
                  <td>
                    <div class="d-flex gap-1">
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-info js-layout-btn"
                        data-room="{{ $room->display_room_number ?? ($room->room_number ?? $room->room_no) }}"
                        data-building="{{ $room->display_building ?? ($room->building ?? 'N/A') }}"
                        data-rows="{{ (int) ($room->display_rows ?? ($room->rows ?? 0)) }}"
                        data-columns="{{ (int) ($room->display_columns ?? ($room->columns ?? 0)) }}"
                        data-bs-toggle="modal"
                        data-bs-target="#seatLayoutModal">
                        <i class="fa fa-th"></i>
                      </button>
                      <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editRoom{{ $room->id }}">
                        <i class="fa fa-edit"></i>
                      </button>
                      <form action="{{ route('coe.exam-rooms.destroy', $room->id) }}" method="post" onsubmit="return confirm('Delete this exam room?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                          <i class="fa fa-trash"></i>
                        </button>
                      </form>
                    </div>

                    <div class="modal fade" id="editRoom{{ $room->id }}" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Edit Exam Room</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <form action="{{ route('coe.exam-rooms.update', $room->id) }}" method="post" class="room-capacity-form">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                              <label class="form-label">Building *</label>
                              <select class="form-select mb-3" name="block_id" required>
                                <option value="">Select Building</option>
                                @foreach($blocks as $block)
                                <option value="{{ $block->id }}" {{ trim((string) ($room->building ?? '')) === trim((string) $block->title) ? 'selected' : '' }}>
                                  {{ $block->title }}
                                </option>
                                @endforeach
                              </select>

                              <label class="form-label">Room Number *</label>
                              <input type="text" class="form-control mb-3" name="room_number" value="{{ $room->display_room_number ?? ($room->room_number ?? $room->room_no) }}" required>

                              <div class="row">
                                <div class="col-md-6">
                                  <label class="form-label">Rows *</label>
                                  <input type="number" class="form-control mb-3 js-rows" name="rows" min="1" value="{{ (int) ($room->rows ?? 1) }}" required>
                                </div>
                                <div class="col-md-6">
                                  <label class="form-label">Columns *</label>
                                  <input type="number" class="form-control mb-3 js-columns" name="columns" min="1" value="{{ (int) ($room->columns ?? 1) }}" required>
                                </div>
                              </div>

                              <div class="row">
                                <div class="col-md-6">
                                  <label class="form-label">Capacity</label>
                                  <input type="text" class="form-control mb-3 js-capacity-display" value="{{ (int) (($room->rows ?? 1) * ($room->columns ?? 1)) }}" readonly>
                                  <small class="text-muted">Auto-calculated from rows x columns.</small>
                                </div>
                                <div class="col-md-6">
                                  <label class="form-label">Priority *</label>
                                  <input type="number" class="form-control mb-3" name="priority" min="1" value="{{ (int) ($room->priority ?? 1) }}" required>
                                </div>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="submit" class="btn btn-primary">Update</button>
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
        </div>
      </div>
    </div>
  </main>
</div>

<div class="modal fade" id="addRoomModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Exam Room</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('coe.exam-rooms.store') }}" method="post" class="room-capacity-form">
        @csrf
        <div class="modal-body">
          <label class="form-label">Building *</label>
          <select class="form-select mb-3" name="block_id" required>
            <option value="">Select Building</option>
            @foreach($blocks as $block)
            <option value="{{ $block->id }}" {{ (string) old('block_id') === (string) $block->id ? 'selected' : '' }}>
              {{ $block->title }}
            </option>
            @endforeach
          </select>

          <label class="form-label">Room Number *</label>
          <input type="text" class="form-control mb-3" name="room_number" value="{{ old('room_number') }}" placeholder="A-101" required>

          <div class="row">
            <div class="col-md-6">
              <label class="form-label">Rows *</label>
              <input type="number" class="form-control mb-3 js-rows" name="rows" min="1" value="{{ old('rows') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Columns *</label>
              <input type="number" class="form-control mb-3 js-columns" name="columns" min="1" value="{{ old('columns') }}" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <label class="form-label">Capacity</label>
              <input type="text" class="form-control mb-3 js-capacity-display" value="0" readonly>
              <small class="text-muted">Auto-calculated from rows x columns.</small>
            </div>
            <div class="col-md-6">
              <label class="form-label">Priority *</label>
              <input type="number" class="form-control mb-3" name="priority" min="1" value="{{ old('priority', 1) }}" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="seatLayoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Seating Layout Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <strong id="layoutRoomLabel">Room</strong>
          <div class="text-muted" id="layoutMeta"></div>
        </div>
        <div id="seatLayoutRows" class="seat-layout-rows"></div>
        <div class="text-muted small mt-2">Each box represents one seat position.</div>
      </div>
    </div>
  </div>
</div>

<style>
  .seat-layout-rows {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-height: 55vh;
    overflow: auto;
    padding: 8px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #f8fafc;
  }

  .seat-row {
    display: grid;
    grid-template-columns: 110px 1fr;
    gap: 8px;
    align-items: center;
  }

  .seat-row-label {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
  }

  .seat-row-grid {
    display: grid;
    gap: 6px;
  }

  .seat-cell {
    min-width: 52px;
    height: 62px;
    border: 1px dashed #cbd5e1;
    border-radius: 8px;
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    padding-top: 4px;
  }

  .desk-top {
    width: 40px;
    height: 22px;
    border: 1px solid #334155;
    border-radius: 4px;
    background: linear-gradient(180deg, #15fad8 0%, #0bedf5 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 700;
    color: #1f2937;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
  }

  .chair-seat {
    width: 22px;
    height: 16px;
    margin-top: 7px;
    border: 1px solid #1d4ed8;
    border-radius: 4px;
    background: linear-gradient(180deg, #93c5fd 0%, #3b82f6 100%);
    position: relative;
  }

  .chair-seat::before {
    content: '';
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    top: -8px;
    width: 14px;
    height: 7px;
    border: 1px solid #1d4ed8;
    border-bottom: none;
    border-radius: 3px 3px 0 0;
    background: #60a5fa;
  }
</style>

<script>
  (function() {
    function parsePositiveInt(value) {
      var num = parseInt(value, 10);
      return Number.isFinite(num) && num > 0 ? num : 0;
    }

    function recalculateCapacity(form) {
      var rowsInput = form.querySelector('.js-rows');
      var columnsInput = form.querySelector('.js-columns');
      var capacityInput = form.querySelector('.js-capacity-display');

      if (!rowsInput || !columnsInput || !capacityInput) {
        return;
      }

      var rows = parsePositiveInt(rowsInput.value);
      var columns = parsePositiveInt(columnsInput.value);
      capacityInput.value = rows * columns;
    }

    function bindCapacityForm(form) {
      var rowsInput = form.querySelector('.js-rows');
      var columnsInput = form.querySelector('.js-columns');

      if (!rowsInput || !columnsInput) {
        return;
      }

      rowsInput.addEventListener('input', function() {
        recalculateCapacity(form);
      });
      columnsInput.addEventListener('input', function() {
        recalculateCapacity(form);
      });

      recalculateCapacity(form);
    }

    function renderSeatLayout(room, building, rows, columns) {
      var titleEl = document.getElementById('layoutRoomLabel');
      var metaEl = document.getElementById('layoutMeta');
      var rowsEl = document.getElementById('seatLayoutRows');

      if (!titleEl || !metaEl || !rowsEl) {
        return;
      }

      titleEl.textContent = (building || 'N/A') + ' - ' + (room || 'Room');
      metaEl.textContent = rows + ' rows x ' + columns + ' columns | Capacity: ' + (rows * columns);

      rowsEl.innerHTML = '';

      if (rows <= 0 || columns <= 0) {
        rowsEl.style.display = 'block';
        var empty = document.createElement('div');
        empty.className = 'text-muted';
        empty.textContent = 'Layout is not available for this room.';
        rowsEl.appendChild(empty);
        return;
      }

      rowsEl.style.display = 'flex';

      var seatNo = 1;
      for (var r = 1; r <= rows; r++) {
        var rowWrap = document.createElement('div');
        rowWrap.className = 'seat-row';

        var rowLabel = document.createElement('div');
        rowLabel.className = 'seat-row-label';
        rowLabel.textContent = 'Row ' + r + ' (' + columns + ' seats)';

        var rowGrid = document.createElement('div');
        rowGrid.className = 'seat-row-grid';
        rowGrid.style.gridTemplateColumns = 'repeat(' + columns + ', minmax(44px, 1fr))';

        for (var c = 1; c <= columns; c++) {
          var seat = document.createElement('div');
          seat.className = 'seat-cell';
          seat.title = 'Row ' + r + ', Column ' + c;

          var deskTop = document.createElement('div');
          deskTop.className = 'desk-top';
          deskTop.textContent = seatNo;

          var chairSeat = document.createElement('div');
          chairSeat.className = 'chair-seat';

          seat.appendChild(deskTop);
          seat.appendChild(chairSeat);

          rowGrid.appendChild(seat);
          seatNo++;
        }

        rowWrap.appendChild(rowLabel);
        rowWrap.appendChild(rowGrid);
        rowsEl.appendChild(rowWrap);
      }
    }

    function bindLayoutButtons() {
      document.querySelectorAll('.js-layout-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
          var room = (btn.getAttribute('data-room') || '').trim();
          var building = (btn.getAttribute('data-building') || '').trim();
          var rows = parsePositiveInt(btn.getAttribute('data-rows'));
          var columns = parsePositiveInt(btn.getAttribute('data-columns'));
          renderSeatLayout(room, building, rows, columns);
        });
      });
    }

    document.querySelectorAll('.room-capacity-form').forEach(bindCapacityForm);
    bindLayoutButtons();
  })();
</script>

@include('includes.footer')