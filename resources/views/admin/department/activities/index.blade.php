<?php

use Illuminate\Support\Facades\Auth;

?>
@include('includes.header')
@include('includes.dept-sidebar')
<!-- Main Content -->
<div class="main-content">
  <!-- Welcome Header -->
  <div class="welcome-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #e9e9e9 40%, #7c3aed 100%)">
    <div>
      <h2 class="mb-1" style="color: #1a1a1a; font-weight: 700;">{{ $subject->title ?? 'Activities Management' }} Department Activities</h2>
      <p class="mb-0" style="color: #6b7280;">Organize and manage all activities related to the {{ $subject->title ?? 'department' }} department.</p>
    </div>
    <div class="d-flex align-items-center gap-3">
      <a href="{{ route('department.dashboard') }}" class="btn btn-modern" style="background: white; color: #5b4cdb;">
        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
      </a>
      <button class="btn btn-modern" style="background: #43cea2; color: white;" data-bs-toggle="modal" data-bs-target="#addActivityModal">
        <i class="fas fa-plus-circle me-2"></i>Add Activity
      </button>
    </div>
  </div>

  <!-- Stats Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="stats-card gradient-green">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;"> Activities</div>
            <div style="font-size: 36px; font-weight: 700;">{{ $stats['total'] }}</div>
          </div>
          <div style="width: 56px; height: 56px; background: rgba(255, 255, 255, 0.2); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-calendar-check" style="font-size: 28px;"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="stats-card gradient-purple">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Upcoming</div>
            <div style="font-size: 36px; font-weight: 700;">{{ $stats['upcoming'] }}</div>
          </div>
          <div style="width: 56px; height: 56px; background: rgba(255, 255, 255, 0.2); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-clock" style="font-size: 28px;"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="stats-card gradient-yellow">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Completed</div>
            <div style="font-size: 36px; font-weight: 700;">{{ $stats['completed'] }}</div>
          </div>
          <div style="width: 56px; height: 56px; background: rgba(255, 255, 255, 0.2); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-check-circle" style="font-size: 28px;"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="stats-card gradient-red">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">This Month</div>
            <div style="font-size: 36px; font-weight: 700;">{{ $stats['this_month'] }}</div>
          </div>
          <div style="width: 56px; height: 56px; background: rgba(255, 255, 255, 0.2); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-calendar-alt" style="font-size: 28px;"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Upcoming Activities -->
  @if(count($upcomingActivities) > 0)
  <div class=" mb-4">
    <div class="p-4">
      <h5 style="color: #1a1a1a; font-weight: 700; margin-bottom: 24px;">
        <i class="fas fa-star me-2" style="color: #fbbf24;"></i>Upcoming Activities
      </h5>
      <div class="row g-3">
        @foreach($upcomingActivities as $activity)
        <div class="col-md-3">
          <div class="action-card" style="background: linear-gradient(135deg, #d2effc 0%, #ffffff 100%);">
            <div class="d-flex align-items-start gap-3">
              <div class="action-card-icon" style="background: rgba(255, 255, 255, 0.2);">
                <i class="fas fa-calendar-day"></i>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-1" style="font-weight: 700;">{{ $activity->title }}</h6>
                <p class="mb-2" style="font-size: 13px; opacity: 0.9;">
                  <i class="fas fa-tag me-1"></i>{{ ucfirst(str_replace('_', ' ', $activity->activity_type)) }}
                </p>
                <p class="mb-2" style="font-size: 13px; opacity: 0.9;">
                  <i class="fas fa-calendar me-1"></i>{{ $activity->formatted_date }}
                  @if($activity->start_time)
                  | <i class="fas fa-clock me-1"></i>{{ date('h:i A', strtotime($activity->start_time)) }}
                  @endif
                </p>
                @if($activity->venue)
                <p class="mb-0" style="font-size: 13px; opacity: 0.9;">
                  <i class="fas fa-map-marker-alt me-1"></i>{{ $activity->venue }}
                </p>
                @endif
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
  @endif

  <!-- All Activities -->
  <div class="table-modern">
    <div class="p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color: #1a1a1a; font-weight: 700; margin: 0;">All Activities</h5>
        <div class="d-flex gap-2">
          <select id="activityTypeFilter" class="form-select" style="width: 200px; border-radius: 10px; border: 1px solid #e5e7eb;">
            <option value="">All Types</option>
            @foreach($activityTypes as $key => $value)
            <option value="{{ $key }}">{{ $value }}</option>
            @endforeach
          </select>
        </div>
      </div>

      @if(count($activities))
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr style="border-bottom: 2px solid #f0f0f0;">
              <th style="color: #fff; font-weight: 600; padding: 16px;">#</th>
              <th style="color: #fff; font-weight: 600;">Title</th>
              <th style="color: #fff; font-weight: 600;">Type</th>
              <th style="color: #fff; font-weight: 600;">Date</th>
              <th style="color: #fff; font-weight: 600;">Venue</th>
              <th style="color: #fff; font-weight: 600;">Status</th>
              <th style="color: #fff; font-weight: 600;">Participants</th>
              <th style="color: #d9f085; font-weight: 600;">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($activities as $activity)
            <tr style="border-bottom: 1px solid #f5f5f5;">
              <td style="padding: 16px; color: #1a1a1a; font-weight: 500;">{{ $loop->iteration }}</td>
              <td style="color: #1a1a1a;">
                <strong>{{ $activity->title }}</strong>
                @if($activity->description)
                <br><small style="color: #6b7280;">{{ Str::limit($activity->description, 50) }}</small>
                @endif
              </td>
              <td>
                <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 6px 12px; border-radius: 8px;">
                  {{ ucfirst(str_replace('_', ' ', $activity->activity_type)) }}
                </span>
              </td>
              <td style="color: #1a1a1a;">
                {{ $activity->formatted_date }}
                @if($activity->start_time)
                <br><small style="color: #6b7280;">{{ date('h:i A', strtotime($activity->start_time)) }}</small>
                @endif
              </td>
              <td style="color: #6b7280;">{{ $activity->venue ?? '-' }}</td>
              <td>
                {{ ucfirst($activity->status) }}
              </td>
              <td style="color: #1a1a1a;">
                {{ $activity->actual_participants ?? $activity->expected_participants ?? '-' }}
              </td>
              <td>
                <div class="d-flex gap-1">
                  <a href="{{ route('department.activities.participants', $activity->id) }}"><button class="btn btn-sm btn-modern view-activity" style="background: #5b4cdb; color: white;">
                      <i class="fas fa-users-cog"></i>
                    </button></a>
                  <button class="btn btn-sm btn-modern edit-activity" data-id="{{ $activity->id }}" style="background: #43cea2; color: white;" data-bs-toggle="modal" data-bs-target="#editActivityModal">
                    <i class="fas fa-edit"></i>
                  </button>
                  <form action="{{ route('department.activities.destroy', $activity->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this activity?');" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-lg btn-danger" style="background: #fee; color: #dc2626; border: none; border-radius: 8px; padding: 6px 12px;">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="mt-4">
        {{ $activities->links() }}
      </div>
      @else
      <div class="text-center py-5">
        <i class="fas fa-calendar-times fa-3x mb-3" style="color: #e5e7eb;"></i>
        <p style="color: #6b7280;">No activities found. Create your first activity!</p>
      </div>
      @endif
    </div>
  </div>

  <!-- Add Activity Modal -->
  <div class="modal fade" id="addActivityModal" tabindex="-1" aria-labelledby="addActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content" style="border-radius: 20px; border: none;">
        <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 24px;">
          <h5 class="modal-title" style="color: #1a1a1a; font-weight: 700;" id="addActivityModalLabel">Add New Activity</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('department.activities.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="subject_id" value="{{ $subject->id }}">
          <div class="modal-body" style="padding: 24px;">
            <div class="row g-3">
              <div class="col-md-8">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Activity Title <span style="color: red;">*</span></label>
                <input type="text" name="title" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;" required>
              </div>
              <div class="col-md-4">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Activity Type <span style="color: red;">*</span></label>
                <select name="activity_type" class="form-select" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;" required>
                  <option value="">Select Type</option>
                  @foreach($activityTypes as $key => $value)
                  <option value="{{ $key }}">{{ $value }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-12">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Description</label>
                <textarea name="description" rows="3" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;"></textarea>
              </div>
              <div class="col-md-4">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Activity Date <span style="color: red;">*</span></label>
                <input type="date" name="activity_date" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;" required>
              </div>
              <div class="col-md-4">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Start Time</label>
                <input type="time" name="start_time" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
              </div>
              <div class="col-md-4">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">End Time</label>
                <input type="time" name="end_time" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
              </div>
              <div class="col-md-6">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Venue</label>
                <input type="text" name="venue" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
              </div>
              <div class="col-md-6">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Expected Participants</label>
                <input type="number" name="expected_participants" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
              </div>
              <div class="col-md-4">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Organizer Name</label>
                <input type="text" name="organizer_name" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
              </div>
              <div class="col-md-4">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Organizer Email</label>
                <input type="email" name="organizer_email" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
              </div>
              <div class="col-md-4">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Organizer Phone</label>
                <input type="text" name="organizer_phone" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
              </div>
              <div class="col-md-4">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Budget (₹)</label>
                <input type="number" step="0.01" name="budget" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
              </div>
              <div class="col-md-4">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Status</label>
                <select name="status" class="form-select" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
                  <option value="planned">Planned</option>
                  <option value="ongoing">Ongoing</option>
                  <option value="completed">Completed</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </div>
              <div class="col-md-4">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Banner Image</label>
                <input type="file" name="banner_image" accept="image/*" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
              </div>
            </div>
          </div>
          <div class="modal-footer" style="border-top: 1px solid #f0f0f0; padding: 24px;">
            <button type="button" class="btn btn-modern" style="background: #f5f7fa; color: #6b7280;" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-modern" style="background: #43cea2; color: white;">Create Activity</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Activity Modal (similar structure, will be populated via JavaScript) -->
  <div class="modal fade" id="editActivityModal" tabindex="-1" aria-labelledby="editActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content" style="border-radius: 20px; border: none;">
        <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 24px;">
          <h5 class="modal-title" style="color: #1a1a1a; font-weight: 700;" id="editActivityModalLabel">Edit Activity</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="editActivityForm" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')
          <div class="modal-body" style="padding: 24px;">
            <div class="row g-3">
              <div class="col-md-8">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Activity Title <span style="color: red;">*</span></label>
                <input type="text" name="title" id="edit_title" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;" required>
              </div>
              <div class="col-md-4">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Activity Type <span style="color: red;">*</span></label>
                <select name="activity_type" id="edit_activity_type" class="form-select" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;" required>
                  <option value="">Select Type</option>
                  @foreach($activityTypes as $key => $value)
                  <option value="{{ $key }}">{{ $value }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-12">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Description</label>
                <textarea name="description" id="edit_description" rows="3" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;"></textarea>
              </div>
              <div class="col-md-4">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Activity Date <span style="color: red;">*</span></label>
                <input type="date" name="activity_date" id="edit_activity_date" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;" required>
              </div>
              <div class="col-md-4">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Start Time</label>
                <input type="time" name="start_time" id="edit_start_time" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
              </div>
              <div class="col-md-4">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">End Time</label>
                <input type="time" name="end_time" id="edit_end_time" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
              </div>
              <div class="col-md-6">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Venue</label>
                <input type="text" name="venue" id="edit_venue" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
              </div>
              <div class="col-md-6">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Expected Participants</label>
                <input type="number" name="expected_participants" id="edit_expected_participants" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
              </div>
              <div class="col-md-6">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Actual Participants</label>
                <input type="number" name="actual_participants" id="edit_actual_participants" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
              </div>
              <div class="col-md-6">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Actual Expense (₹)</label>
                <input type="number" step="0.01" name="actual_expense" id="edit_actual_expense" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
              </div>
              <div class="col-md-4">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Organizer Name</label>
                <input type="text" name="organizer_name" id="edit_organizer_name" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
              </div>
              <div class="col-md-4">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Organizer Email</label>
                <input type="email" name="organizer_email" id="edit_organizer_email" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
              </div>
              <div class="col-md-4">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Organizer Phone</label>
                <input type="text" name="organizer_phone" id="edit_organizer_phone" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
              </div>
              <div class="col-md-4">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Budget (₹)</label>
                <input type="number" step="0.01" name="budget" id="edit_budget" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
              </div>
              <div class="col-md-4">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Status</label>
                <select name="status" id="edit_status" class="form-select" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
                  <option value="planned">Planned</option>
                  <option value="ongoing">Ongoing</option>
                  <option value="completed">Completed</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </div>
              <div class="col-md-4">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Banner Image</label>
                <input type="file" name="banner_image" accept="image/*" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
              </div>
              <div class="col-md-12">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Remarks</label>
                <textarea name="remarks" id="edit_remarks" rows="2" class="form-control" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer" style="border-top: 1px solid #f0f0f0; padding: 24px;">
            <button type="button" class="btn btn-modern" style="background: #f5f7fa; color: #6b7280;" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-modern" style="background: #43cea2; color: white;">Update Activity</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- View Activity Modal -->
  <div class="modal fade" id="viewActivityModal" tabindex="-1" aria-labelledby="viewActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content" style="border-radius: 20px; border: none;">
        <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 24px;">
          <h5 class="modal-title" style="color: #1a1a1a; font-weight: 700;" id="viewActivityModalLabel">Activity Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="padding: 24px;" id="viewActivityContent">
          <!-- Content will be populated via JavaScript -->
        </div>
      </div>
    </div>
  </div>

</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Edit activity
    document.querySelectorAll('.edit-activity').forEach(button => {
      button.addEventListener('click', function() {
        const activityId = this.getAttribute('data-id');
        fetch(`/deptartment/activities/${activityId}/show`)
          .then(response => response.json())
          .then(data => {
            document.getElementById('edit_title').value = data.title;
            document.getElementById('edit_activity_type').value = data.activity_type;
            document.getElementById('edit_description').value = data.description || '';
            document.getElementById('edit_activity_date').value = data.activity_date;
            document.getElementById('edit_start_time').value = data.start_time || '';
            document.getElementById('edit_end_time').value = data.end_time || '';
            document.getElementById('edit_venue').value = data.venue || '';
            document.getElementById('edit_expected_participants').value = data.expected_participants || '';
            document.getElementById('edit_actual_participants').value = data.actual_participants || '';
            document.getElementById('edit_organizer_name').value = data.organizer_name || '';
            document.getElementById('edit_organizer_email').value = data.organizer_email || '';
            document.getElementById('edit_organizer_phone').value = data.organizer_phone || '';
            document.getElementById('edit_budget').value = data.budget || '';
            document.getElementById('edit_actual_expense').value = data.actual_expense || '';
            document.getElementById('edit_status').value = data.status;
            document.getElementById('edit_remarks').value = data.remarks || '';

            document.getElementById('editActivityForm').action = `/deptartment/activities/${activityId}`;
          });
      });
    });

    // View activity
    document.querySelectorAll('.view-activity').forEach(button => {
      button.addEventListener('click', function() {
        const activityId = this.getAttribute('data-id');
        fetch(`/deptartment/activities/${activityId}/show`)
          .then(response => response.json())
          .then(data => {
            const content = `
            <div class="row g-3">
              <div class="col-12">
                <h4 style="color: #1a1a1a; font-weight: 700;">${data.title}</h4>
                <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 6px 12px; border-radius: 8px;">
                  ${data.activity_type.replace('_', ' ').toUpperCase()}
                </span>
                <span class="badge badge-${data.status_badge} ms-2" style="padding: 6px 12px; border-radius: 8px;">
                  ${data.status.toUpperCase()}
                </span>
              </div>
              ${data.description ? `<div class="col-12"><p style="color: #6b7280;">${data.description}</p></div>` : ''}
              <div class="col-md-6">
                <strong style="color: #1a1a1a;">Date:</strong><br>
                <span style="color: #6b7280;">${new Date(data.activity_date).toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'})}</span>
              </div>
              ${data.start_time ? `
              <div class="col-md-6">
                <strong style="color: #1a1a1a;">Time:</strong><br>
                <span style="color: #6b7280;">${data.start_time}${data.end_time ? ' - ' + data.end_time : ''}</span>
              </div>` : ''}
              ${data.venue ? `
              <div class="col-md-6">
                <strong style="color: #1a1a1a;">Venue:</strong><br>
                <span style="color: #6b7280;">${data.venue}</span>
              </div>` : ''}
              ${data.organizer_name ? `
              <div class="col-md-6">
                <strong style="color: #1a1a1a;">Organizer:</strong><br>
                <span style="color: #6b7280;">${data.organizer_name}</span>
                ${data.organizer_email ? `<br><small>${data.organizer_email}</small>` : ''}
                ${data.organizer_phone ? `<br><small>${data.organizer_phone}</small>` : ''}
              </div>` : ''}
              ${data.expected_participants ? `
              <div class="col-md-6">
                <strong style="color: #1a1a1a;">Expected Participants:</strong><br>
                <span style="color: #6b7280;">${data.expected_participants}</span>
              </div>` : ''}
              ${data.actual_participants ? `
              <div class="col-md-6">
                <strong style="color: #1a1a1a;">Actual Participants:</strong><br>
                <span style="color: #6b7280;">${data.actual_participants}</span>
              </div>` : ''}
              ${data.budget ? `
              <div class="col-md-6">
                <strong style="color: #1a1a1a;">Budget:</strong><br>
                <span style="color: #6b7280;">₹${parseFloat(data.budget).toLocaleString()}</span>
              </div>` : ''}
              ${data.actual_expense ? `
              <div class="col-md-6">
                <strong style="color: #1a1a1a;">Actual Expense:</strong><br>
                <span style="color: #6b7280;">₹${parseFloat(data.actual_expense).toLocaleString()}</span>
              </div>` : ''}
              ${data.remarks ? `
              <div class="col-12">
                <strong style="color: #1a1a1a;">Remarks:</strong><br>
                <p style="color: #6b7280;">${data.remarks}</p>
              </div>` : ''}
            </div>
          `;
            document.getElementById('viewActivityContent').innerHTML = content;
          });
      });
    });

    // Filter by activity type
    document.getElementById('activityTypeFilter').addEventListener('change', function() {
      const type = this.value;
      const url = new URL(window.location.href);
      if (type) {
        url.searchParams.set('type', type);
      } else {
        url.searchParams.delete('type');
      }
      window.location.href = url.toString();
    });
  });
</script>

@include('includes.footer')