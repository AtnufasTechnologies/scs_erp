@include('includes.header')
<div class="wrapper">
  @include('student-affairs.sidebar')
  <main class="page-content">
    <div class="container-fluid py-3">
      <h3>Attendance Regularization</h3>

      <div class="card shadow-sm mb-3">
        <div class="card-header">Approve Regularization by Event</div>
        <div class="card-body">
          <form id="regFilterForm" class="row g-2">
            <div class="col-md-2">
              <select name="event_source" id="event_source" class="form-select">
                <option value="ec_event" {{ $eventSource === 'ec_event' ? 'selected' : '' }}>Event Module</option>
                <option value="department_activity" {{ $eventSource === 'department_activity' ? 'selected' : '' }}>Department Activity</option>
              </select>
            </div>
            <div class="col-md-4">
              <select name="event_id" id="event_id" class="form-select" required>
                <option value="">Select approved event</option>
                @foreach($events as $event)
                <option value="{{ $event['id'] }}" data-start="{{ $event['start_date'] }}" data-end="{{ $event['end_date'] }}">{{ $event['title'] }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2"><input type="date" name="start_date" id="start_date" class="form-control" required></div>
            <div class="col-md-2"><input type="date" name="end_date" id="end_date" class="form-control" required></div>
            <div class="col-md-2"><button type="button" id="previewBtn" class="btn btn-primary w-100">Preview Absences</button></div>
          </form>

          <div class="mt-3 table-responsive">
            <table class="table table-sm table-bordered" id="previewTable">
              <thead>
                <tr>
                  <th>Select</th>
                  <th>Student</th>
                  <th>Roll</th>
                  <th>Date</th>
                  <th>Original</th>
                  <th>Effective</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>

          <div class="mt-2">
            <textarea id="remarks" class="form-control" placeholder="Approval remarks"></textarea>
            <button type="button" id="approveBtn" class="btn btn-success mt-2">Approve Selected (Absent -> Present)</button>
          </div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header">Regularization History</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Request No</th>
                <th>Source</th>
                <th>Event</th>
                <th>Date Range</th>
                <th>Items</th>
                <th>Approved</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($history as $h)
              <tr>
                <td>{{ $h->request_no }}</td>
                <td>{{ $h->event_source }}</td>
                <td>{{ $h->event_id }}</td>
                <td>{{ optional($h->event_start_date)->format('d-M-Y') }} to {{ optional($h->event_end_date)->format('d-M-Y') }}</td>
                <td>{{ $h->items_count }}</td>
                <td>{{ optional($h->approved_at)->format('d-M-Y H:i') }}</td>
                <td><a class="btn btn-sm btn-outline-primary" href="{{ route('dean.attendance.regularization.history', $h->id) }}">View</a></td>
              </tr>
              @endforeach
            </tbody>
          </table>
          {{ $history->links() }}
        </div>
      </div>
    </div>
  </main>
</div>
@include('includes.footer')
<script>
  const eventSelect = document.getElementById('event_id');
  const startDateInput = document.getElementById('start_date');
  const endDateInput = document.getElementById('end_date');
  const previewBtn = document.getElementById('previewBtn');
  const approveBtn = document.getElementById('approveBtn');
  const previewTableBody = document.querySelector('#previewTable tbody');

  if (eventSelect) {
    eventSelect.addEventListener('change', function() {
      const selected = eventSelect.options[eventSelect.selectedIndex];
      startDateInput.value = selected?.dataset?.start || '';
      endDateInput.value = selected?.dataset?.end || '';
    });
  }

  async function previewRows() {
    const payload = {
      event_source: document.getElementById('event_source').value,
      event_id: eventSelect.value,
      start_date: startDateInput.value,
      end_date: endDateInput.value,
    };

    const response = await fetch("{{ route('dean.attendance.regularization.preview') }}", {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    });

    const data = await response.json();
    previewTableBody.innerHTML = '';
    (data.rows || []).forEach((row) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><input type="checkbox" class="reg-item" value="${row.attendance_id}" checked></td>
        <td>${row.student_name}</td>
        <td>${row.roll_no}</td>
        <td>${row.date}</td>
        <td>${row.original_status}</td>
        <td><span class="badge bg-success">${row.effective_status}</span></td>
      `;
      previewTableBody.appendChild(tr);
    });
  }

  if (previewBtn) {
    previewBtn.addEventListener('click', previewRows);
  }

  if (approveBtn) {
    approveBtn.addEventListener('click', async function() {
      const ids = Array.from(document.querySelectorAll('.reg-item:checked')).map(el => Number(el.value));
      if (ids.length === 0) {
        alert('Please select at least one absence row.');
        return;
      }

      const payload = {
        event_source: document.getElementById('event_source').value,
        event_id: eventSelect.value,
        start_date: startDateInput.value,
        end_date: endDateInput.value,
        attendance_ids: ids,
        remarks: document.getElementById('remarks').value || null,
      };

      const response = await fetch("{{ route('dean.attendance.regularization.approve') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
      });

      const data = await response.json();
      if (!response.ok || !data.status) {
        alert(data.message || 'Approval failed');
        return;
      }

      alert(data.message + ' Request No: ' + data.request_no);
      window.location.reload();
    });
  }
</script>