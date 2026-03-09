<?php

use Illuminate\Support\Str;
?>

@include('includes.header')
@include('admin.sidebar')

<div class="page-content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">User Activity Logs</h5>
        <span class="badge bg-success">Total: {{ $logs->total() }}</span>
      </div>

      <div class="card-body border-bottom">
        <form method="GET" action="{{ route('admin.user.activity-logs') }}" class="row g-3">
          <div class="col-md-3">
            <label class="form-label">User</label>
            <select name="user_id" class="form-select dselect-example">
              <option value="">All Users</option>
              @foreach($users as $user)
              <option value="{{ $user->id }}" {{ (string) request('user_id') === (string) $user->id ? 'selected' : '' }}>
                {{ $user->name }} ({{ $user->email }})
              </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label">Event</label>
            <select name="event" class="form-select">
              <option value="">All</option>
              <option value="created" {{ request('event') === 'created' ? 'selected' : '' }}>Created</option>
              <option value="updated" {{ request('event') === 'updated' ? 'selected' : '' }}>Updated</option>
              <option value="deleted" {{ request('event') === 'deleted' ? 'selected' : '' }}>Deleted</option>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label">From Date</label>
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
          </div>

          <div class="col-md-2">
            <label class="form-label">To Date</label>
            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
          </div>

          <div class="col-md-3">
            <label class="form-label">Keyword</label>
            <input type="text" name="keyword" class="form-control" placeholder="Model, ID, URL..." value="{{ request('keyword') }}">
          </div>

          <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="{{ route('admin.user.activity-logs') }}" class="btn btn-light border">Reset</a>
          </div>
        </form>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-sm align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>Time</th>
                <th>User</th>
                <th>Event</th>
                <th>Model</th>
                <th>Record ID</th>
                <th>Old Values</th>
                <th>New Values</th>
                <th>IP</th>
              </tr>
            </thead>
            <tbody>
              @forelse($logs as $log)
              <tr>
                <td>{{ $logs->firstItem() + $loop->index }}</td>
                <td>{{ optional($log->created_at)->format('d M Y h:i A') }}</td>
                <td>
                  @if($log->user)
                  <div>{{ $log->user->name }}</div>
                  <small class="text-muted">{{ $log->user->email }}</small>
                  @else
                  <span class="text-muted">System/Guest</span>
                  @endif
                </td>
                <td>
                  @php
                  $eventClass = $log->event === 'created' ? 'success' : ($log->event === 'updated' ? 'warning' : 'danger');
                  @endphp
                  <span class="badge bg-{{ $eventClass }}">{{ ucfirst($log->event) }}</span>
                </td>
                <td>{{ Str::afterLast($log->auditable_type, '\\') }}</td>
                <td>{{ $log->auditable_id }}</td>
                <td>
                  @if(!empty($log->old_values))
                  <pre class="mb-0" style="white-space: pre-wrap; max-width: 260px;">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                  @else
                  <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  @if(!empty($log->new_values))
                  <pre class="mb-0" style="white-space: pre-wrap; max-width: 260px;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                  @else
                  <span class="text-muted">-</span>
                  @endif
                </td>
                <td>{{ $log->ip_address ?? '-' }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="9" class="text-center text-muted">No activity logs found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="mt-3">
          {{ $logs->links() }}
        </div>
      </div>
    </div>
  </div>
</div>

@include('includes.footer')