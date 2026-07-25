@include('includes.header')
@include('admin.sidebar')

<h3><span class="text-uppercase">Hour Master</span></h3>

@if (session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
<div class="alert alert-danger">
  <ul class="mb-0">
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif

@php
$shiftOptions = \App\Models\ShiftMaster::orderBy('sort_order')->orderBy('title')->get();
@endphp

<!-- Button trigger modal -->
<button class="cst-button mb-3" style="--clr: #21d9c7ff;" data-bs-toggle="modal" data-bs-target="#exampleModal">
  <span class="button-decor"></span>
  <div class="button-content">
    <div class="button__icon">
      <i class="fa fa-plus-circle"></i>
    </div>
    <span class="button__text">Add New</span>
  </div>
</button>



<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">New </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{url('erp/admin/master/hour')}}" method="post">
        @csrf
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Hour No *</label>
              <input type="number" min="1" name="hour" class="form-control" value="{{ old('hour') }}" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Shift</label>
              <select name="shift_id" class="form-select">
                <option value="">Select Shift</option>
                @foreach($shiftOptions as $shift)
                <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}>
                  {{ $shift->shiftname ?? $shift->title }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Display Name</label>
              <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Hour 1">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Teaching Hour</label>
              <select name="is_teaching" class="form-select">
                <option value="1" {{ old('is_teaching', '1') == '1' ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ old('is_teaching') == '0' ? 'selected' : '' }}>No</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Start Time</label>
              <input type="time" name="start_time" class="form-control" value="{{ old('start_time') }}">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">End Time</label>
              <input type="time" name="end_time" class="form-control" value="{{ old('end_time') }}">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
              </select>
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
<div class="container-fluid card shadow">

  <table class="table mt-3 mb-3">
    <thead>
      <tr>
        <th>#</th>
        <th>Hour</th>
        <th>Name</th>
        <th>Shift</th>
        <th>Time</th>
        <th>Teaching</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @if (count($data))
      @php
      $sl = 1;
      $groupedData = collect($data)
      ->sortBy(function ($item) {
      return [
      $item->shift_id ?? 0,
      $item->hour_no ?? $item->title ?? 0,
      ];
      })
      ->groupBy(function ($item) {
      return $item->shiftmaster->shiftname ?? $item->shiftmaster->title ?? 'No Shift';
      });
      @endphp

      @foreach ($groupedData as $shiftTitle => $items)
      <tr class="table-light">
        <td colspan="8" class="fw-bold">Shift: {{ $shiftTitle }}</td>
      </tr>
      @foreach ($items as $item)
      <tr>
        <td>{{$sl++}}</td>
        <td>{{ $item->hour_no ?? $item->title ?? '-' }}</td>
        <td>{{ $item->name ?? ('Hour '.($item->hour_no ?? $item->title ?? '')) }}</td>
        <td>{{ $item->shiftmaster->shiftname ?? $item->shiftmaster->title ?? '-' }}</td>
        <td>
          @if(!empty($item->start_time) && !empty($item->end_time))
          {{ \Carbon\Carbon::parse($item->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($item->end_time)->format('h:i A') }}
          @else
          -
          @endif
        </td>
        <td>
          @if(isset($item->is_teaching))
          <span class="badge {{ $item->is_teaching ? 'bg-success' : 'bg-secondary' }}">{{ $item->is_teaching ? 'Yes' : 'No' }}</span>
          @else
          -
          @endif
        </td>
        <td>
          @if(isset($item->status))
          <span class="badge {{ $item->status ? 'bg-success' : 'bg-danger' }}">{{ $item->status ? 'Active' : 'Inactive' }}</span>
          @else
          -
          @endif
        </td>
        <td>
          <button
            type="button"
            class="btn btn-outline-primary btn-sm edit-hour-btn"
            data-bs-toggle="modal"
            data-bs-target="#editHourModal"
            data-id="{{ $item->id }}"
            data-hour="{{ $item->hour_no ?? $item->title }}"
            data-shift-id="{{ $item->shift_id }}"
            data-name="{{ $item->name ?? ('Hour '.($item->hour_no ?? $item->title)) }}"
            data-is-teaching="{{ isset($item->is_teaching) ? (int) $item->is_teaching : 1 }}"
            data-start-time="{{ !empty($item->start_time) ? \Carbon\Carbon::parse($item->start_time)->format('H:i') : '' }}"
            data-end-time="{{ !empty($item->end_time) ? \Carbon\Carbon::parse($item->end_time)->format('H:i') : '' }}"
            data-status="{{ isset($item->status) ? (int) $item->status : 1 }}">
            <i class="fas fa-edit"></i>
          </button>
          <a href="{{url('erp/admin/master/delhour/'.$item->id)}}" id="citadel"><button class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt"></i></button></a>
        </td>
      </tr>
      @endforeach
      @endforeach

      @else
      <tr>
        <td colspan="8" class="text-center">No Records</td>
      </tr>
      @endif
    </tbody>

  </table>
</div>

<div class="modal fade" id="editHourModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Hour</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editHourForm" method="post">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Hour No *</label>
              <input type="number" min="1" id="edit_hour" name="hour" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Shift</label>
              <select id="edit_shift_id" name="shift_id" class="form-select">
                <option value="">Select Shift</option>
                @foreach($shiftOptions as $shift)
                <option value="{{ $shift->id }}">{{ $shift->shiftname ?? $shift->title }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Display Name</label>
              <input type="text" id="edit_name" name="name" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Teaching Hour</label>
              <select id="edit_is_teaching" name="is_teaching" class="form-select">
                <option value="1">Yes</option>
                <option value="0">No</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Start Time</label>
              <input type="time" id="edit_start_time" name="start_time" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">End Time</label>
              <input type="time" id="edit_end_time" name="end_time" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Status</label>
              <select id="edit_status" name="status" class="form-select">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.querySelectorAll('.edit-hour-btn').forEach(function(button) {
    button.addEventListener('click', function() {
      const id = this.getAttribute('data-id');
      document.getElementById('editHourForm').action = "{{ url('erp/admin/master/hour') }}/" + id;
      document.getElementById('edit_hour').value = this.getAttribute('data-hour') || '';
      document.getElementById('edit_shift_id').value = this.getAttribute('data-shift-id') || '';
      document.getElementById('edit_name').value = this.getAttribute('data-name') || '';
      document.getElementById('edit_is_teaching').value = this.getAttribute('data-is-teaching') || '1';
      document.getElementById('edit_start_time').value = this.getAttribute('data-start-time') || '';
      document.getElementById('edit_end_time').value = this.getAttribute('data-end-time') || '';
      document.getElementById('edit_status').value = this.getAttribute('data-status') || '1';
    });
  });
</script>
@include('includes.footer')