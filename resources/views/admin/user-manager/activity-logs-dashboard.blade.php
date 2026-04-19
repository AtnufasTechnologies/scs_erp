<?php

use Illuminate\Support\Str;
?>

@include('includes.header')
@include('admin.sidebar')

<div class="page-content">
  <div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-0" style="color: #1a1a1a; font-weight: 700;">Activity Logs Dashboard</h4>
        <small style="color: #6b7280;">Real-time system audit & monitoring</small>
      </div>
      <a href="{{ route('admin.user.activityapplication-single-logs') }}" class="btn btn-primary">
        <i class="fas fa-list"></i> View All Logs
      </a>
    </div>

    <!-- Overall Statistics -->
    <div class="row g-4 mb-4">
      <div class="col-md-3">
        <div class="card border-0 rounded-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <p class="mb-2" style="opacity: 0.9; font-size: 14px;">Total Activities</p>
                <h3 class="mb-1" style="font-weight: 700;">{{ $totalActivityCount }}</h3>
                <small style="opacity: 0.8;">All time</small>
              </div>
              <i class="fas fa-chart-line fa-2x" style="opacity: 0.3;"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card border-0 rounded-3" style="background: linear-gradient(135deg, #43cea2 0%, #0efab3 100%); color: white; box-shadow: 0 8px 24px rgba(67, 206, 162, 0.3);">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <p class="mb-2" style="opacity: 0.9; font-size: 14px;">Created</p>
                <h3 class="mb-1" style="font-weight: 700;">{{ $totalCreated }}</h3>
                <small style="opacity: 0.8;">New records</small>
              </div>
              <i class="fas fa-plus-circle fa-2x" style="opacity: 0.3;"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card border-0 rounded-3" style="background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); color: white; box-shadow: 0 8px 24px rgba(247, 151, 30, 0.3);">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <p class="mb-2" style="opacity: 0.9; font-size: 14px;">Updated</p>
                <h3 class="mb-1" style="font-weight: 700;">{{ $totalUpdated }}</h3>
                <small style="opacity: 0.8;">Modified records</small>
              </div>
              <i class="fas fa-edit fa-2x" style="opacity: 0.3;"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card border-0 rounded-3" style="background: linear-gradient(135deg, #ff9966 0%, #ff5e62 100%); color: white; box-shadow: 0 8px 24px rgba(255, 153, 102, 0.3);">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <p class="mb-2" style="opacity: 0.9; font-size: 14px;">Deleted</p>
                <h3 class="mb-1" style="font-weight: 700;">{{ $totalDeleted }}</h3>
                <small style="opacity: 0.8;">Removed records</small>
              </div>
              <i class="fas fa-trash fa-2x" style="opacity: 0.3;"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Today's Activity -->
    <div class="row g-4 mb-4">
      <div class="col-md-12">
        <div class="card border-0 rounded-3" style="box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);">
          <div class="card-header bg-white border-bottom-0 p-4">
            <h6 class="mb-0" style="color: #1a1a1a; font-weight: 700;">Today's Activity</h6>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-3">
                <div style="background: #f3f4f6; padding: 20px; border-radius: 12px; text-align: center;">
                  <div style="font-size: 32px; font-weight: 700; color: #667eea;">{{ $todayActivity }}</div>
                  <div style="color: #6b7280; font-size: 14px; margin-top: 8px;">Total Activities</div>
                </div>
              </div>
              <div class="col-md-3">
                <div style="background: #ecfdf5; padding: 20px; border-radius: 12px; text-align: center;">
                  <div style="font-size: 32px; font-weight: 700; color: #43cea2;">{{ $todayCreated }}</div>
                  <div style="color: #6b7280; font-size: 14px; margin-top: 8px;">Created</div>
                </div>
              </div>
              <div class="col-md-3">
                <div style="background: #fef3c7; padding: 20px; border-radius: 12px; text-align: center;">
                  <div style="font-size: 32px; font-weight: 700; color: #f7971e;">{{ $todayUpdated }}</div>
                  <div style="color: #6b7280; font-size: 14px; margin-top: 8px;">Updated</div>
                </div>
              </div>
              <div class="col-md-3">
                <div style="background: #fee2e2; padding: 20px; border-radius: 12px; text-align: center;">
                  <div style="font-size: 32px; font-weight: 700; color: #ff5e62;">{{ $todayDeleted }}</div>
                  <div style="color: #6b7280; font-size: 14px; margin-top: 8px;">Deleted</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts & Insights -->
    <div class="row g-4 mb-4">
      <!-- Activity Timeline -->
      <div class="col-md-6">
        <div class="card border-0 rounded-3" style="box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);">
          <div class="card-header bg-white border-bottom-0 p-4">
            <h6 class="mb-0" style="color: #1a1a1a; font-weight: 700;">Last 7 Days Activity</h6>
          </div>
          <div class="card-body">
            <div style="display: flex; align-items: flex-end; gap: 12px; height: 200px; justify-content: space-around;">
              @foreach($activityTimeline as $day)
              <div style="display: flex; flex-direction: column; align-items: center; flex: 1;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 100%; border-radius: 8px 8px 0 0; height: {{ max($day['count'] * 5, 20) }}px; transition: all 0.3s; cursor: pointer;" title="{{ $day['count'] }} activities"></div>
                <small style="color: #6b7280; margin-top: 8px; font-size: 12px;">{{ $day['date'] }}</small>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>

      <!-- Activity by Model -->
      <div class="col-md-6">
        <div class="card border-0 rounded-3" style="box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);">
          <div class="card-header bg-white border-bottom-0 p-4">
            <h6 class="mb-0" style="color: #1a1a1a; font-weight: 700;">Top Models (Last 30 Days)</h6>
          </div>
          <div class="card-body">
            @forelse($activityByModel as $model => $count)
            <?php
            $maxCount = $activityByModel->values()->max();
            $percentage = $maxCount > 0 ? min(($count / $maxCount) * 100, 100) : 0;
            ?>
            <div style="margin-bottom: 16px;">
              <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                <span style="color: #1a1a1a; font-weight: 500;">{{ $model }}</span>
                <span style="color: #667eea; font-weight: 700;">{{ $count }}</span>
              </div>
              <div style="background: #f3f4f6; border-radius: 8px; height: 6px; overflow: hidden;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 100%; width: {{ $percentage }}%;"></div>
              </div>
            </div>
            @empty
            <p class="text-muted text-center">No activity data available</p>
            @endforelse
          </div>
        </div>
      </div>
    </div>

    <!-- Most Active Users & Recent Activity -->
    <div class="row g-4">
      <!-- Most Active Users -->
      <div class="col-md-6">
        <div class="card border-0 rounded-3" style="box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);">
          <div class="card-header bg-white border-bottom-0 p-4">
            <h6 class="mb-0" style="color: #1a1a1a; font-weight: 700;">Most Active Users (Last 7 Days)</h6>
          </div>
          <div class="card-body">
            <div style="max-height: 300px; overflow-y: auto;">
              @forelse($mostActiveUsers as $index => $userActivity)
              <div style="display: flex; align-items: center; padding: 12px 0; border-bottom: 1px solid #f3f4f6;">
                <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; margin-right: 12px;">{{ $index + 1 }}</div>
                <div style="flex: 1;">
                  <div style="color: #1a1a1a; font-weight: 500;">{{ $userActivity['user'] }}</div>
                  <small style="color: #6b7280;">{{ $userActivity['count'] }} activities</small>
                </div>
                <div style="background: #f3f4f6; padding: 4px 12px; border-radius: 20px; font-weight: 600; color: #667eea;">{{ $userActivity['count'] }}</div>
              </div>
              @empty
              <p class="text-muted text-center">No user activity data</p>
              @endforelse
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activities -->
      <div class="col-md-6">
        <div class="card border-0 rounded-3" style="box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);">
          <div class="card-header bg-white border-bottom-0 p-4">
            <h6 class="mb-0" style="color: #1a1a1a; font-weight: 700;">Recent Activities</h6>
          </div>
          <div class="card-body">
            <div style="max-height: 300px; overflow-y: auto;">
              @forelse($recentActivities as $activity)
              <?php
              $bgColor = $activity->event === 'created' ? '#ecfdf5' : ($activity->event === 'updated' ? '#fef3c7' : '#fee2e2');
              $textColor = $activity->event === 'created' ? '#43cea2' : ($activity->event === 'updated' ? '#f7971e' : '#ff5e62');
              $icon = $activity->event === 'created' ? 'fas fa-plus' : ($activity->event === 'updated' ? 'fas fa-edit' : 'fas fa-trash');
              ?>
              <div style="display: flex; align-items: center; padding: 12px 0; border-bottom: 1px solid #f3f4f6;">
                <div style="width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 12px; background: {{ $bgColor }}; color: {{ $textColor }};">
                  <i class="{{ $icon }}"></i>
                </div>
                <div style="flex: 1; min-width: 0;">
                  <div style="color: #1a1a1a; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ Str::afterLast($activity->auditable_type, '\\') }} #{{ $activity->auditable_id }}
                  </div>
                  <small style="color: #6b7280;">
                    {{ $activity->user ? $activity->user->name : 'System' }} • {{ optional($activity->created_at)->diffForHumans() }}
                  </small>
                </div>
              </div>
              @empty
              <p class="text-muted text-center">No activity found</p>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<style>
  /* Smooth scrollbar for activity lists */
  ::-webkit-scrollbar {
    width: 6px;
  }

  ::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
  }

  ::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
  }

  ::-webkit-scrollbar-thumb:hover {
    background: #888;
  }
</style>

@include('includes.footer')