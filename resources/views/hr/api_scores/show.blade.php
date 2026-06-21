<?php

use App\Http\Controllers\StaticController;

$userRoleType = StaticController::fetchUserRole();
?>
@include('includes.header')

<div class="wrapper">
  @if($userRoleType == 'principal' || $userRoleType == 'vice-principal' || $userRoleType == 'bursar')
  @include('principal.sidebar')
  @else
  @include('hr.sidebar')
  @endif

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">API Score Details</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('hr.api-scores.index') }}">API Scores</a></li>
            <li class="breadcrumb-item active">Details</li>
          </ol>
        </nav>
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
      <div class="col-md-4">
        <div class="card">
          <div class="card-body text-center">
            <h5>{{ $score->faculty->FIRST_NAME }} {{ $score->faculty->LAST_NAME }}</h5>
            <p class="text-muted">{{ $score->faculty->USER_CODE }}</p>
            <p class="text-muted">{{ $score->academicYear->year_name }}</p>

            <div class="mt-3">
              <h2 class="text-primary">{{ number_format($score->total_score, 2) }}</h2>
              <p class="text-muted">Total API Score / 100</p>
            </div>

            <div class="mt-3">
              @if($score->status == 'draft')
              <span class="badge bg-secondary">Draft</span>
              @elseif($score->status == 'submitted')
              <span class="badge bg-warning">Submitted</span>
              @elseif($score->status == 'verified')
              <span class="badge bg-info">Verified</span>
              @elseif($score->status == 'approved')
              <span class="badge bg-success">Approved</span>
              @endif
            </div>
            @if($userRoleType == 'hr')
            <div class="mt-3 d-flex gap-2 justify-content-center">
              <a href="{{ route('hr.api-scores.edit', $score->id) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit me-1"></i>Edit
              </a>
              <a href="{{ route('hr.api-scores.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back
              </a>
            </div>
            @endif

            @if($score->status == 'draft')
            <form method="POST" action="{{ route('hr.api-scores.submit', $score->id) }}" class="mt-2">
              @csrf
              <button type="submit" class="btn btn-success btn-sm w-100">
                <i class="fas fa-paper-plane me-1"></i>Submit for Verification
              </button>
            </form>
            @endif

            @if($score->status == 'submitted')
            <form method="POST" action="{{ route('hr.api-scores.verify', $score->id) }}" class="mt-2">
              @csrf
              <button type="submit" class="btn btn-warning btn-sm w-100">
                <i class="fas fa-check-circle me-1"></i>Verify & Approve
              </button>
            </form>
            @endif
          </div>
        </div>

        @if($score->verified_at)
        <div class="card mt-3">
          <div class="card-header">
            <h6 class="mb-0">Verification Details</h6>
          </div>
          <div class="card-body">
            <p><strong>Verified By:</strong> {{ $score->verifiedByUser->name ?? 'N/A' }}</p>
            <p><strong>Verified On:</strong> {{ $score->verified_at->format('d M Y H:i') }}</p>
          </div>
        </div>
        @endif
      </div>

      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0">Category-wise Breakdown</h6>
          </div>
          <div class="card-body">
            <table class="table table-bordered">
              <thead class="table-light">
                <tr>
                  <th>Category</th>
                  <th>Description</th>
                  <th>Score</th>
                  <th>Max Score</th>
                  <th>Percentage</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><strong>I</strong></td>
                  <td>Teaching Output</td>
                  <td>{{ number_format($score->category_i_score, 2) }}</td>
                  <td>10</td>
                  <td>{{ number_format(($score->category_i_score / 10) * 100, 1) }}%</td>
                </tr>
                <tr>
                  <td><strong>II</strong></td>
                  <td>Teaching, Learning & Evaluation</td>
                  <td>{{ number_format($score->category_ii_score, 2) }}</td>
                  <td>25</td>
                  <td>{{ number_format(($score->category_ii_score / 25) * 100, 1) }}%</td>
                </tr>
                <tr>
                  <td><strong>III</strong></td>
                  <td>Cocurricular & Extension Activities</td>
                  <td>{{ number_format($score->category_iii_score, 2) }}</td>
                  <td>10</td>
                  <td>{{ number_format(($score->category_iii_score / 10) * 100, 1) }}%</td>
                </tr>
                <tr>
                  <td><strong>IV</strong></td>
                  <td>Managerial Contributions</td>
                  <td>{{ number_format($score->category_iv_score, 2) }}</td>
                  <td>25</td>
                  <td>{{ number_format(($score->category_iv_score / 25) * 100, 1) }}%</td>
                </tr>
                <tr>
                  <td><strong>V</strong></td>
                  <td>Professional Development</td>
                  <td>{{ number_format($score->category_v_score, 2) }}</td>
                  <td>15</td>
                  <td>{{ number_format(($score->category_v_score / 15) * 100, 1) }}%</td>
                </tr>
                <tr>
                  <td><strong>VI</strong></td>
                  <td>Academic Activities</td>
                  <td>{{ number_format($score->category_vi_score, 2) }}</td>
                  <td>10</td>
                  <td>{{ number_format(($score->category_vi_score / 10) * 100, 1) }}%</td>
                </tr>
                <tr>
                  <td><strong>VII</strong></td>
                  <td>Documentation</td>
                  <td>{{ number_format($score->category_vii_score, 2) }}</td>
                  <td>5</td>
                  <td>{{ number_format(($score->category_vii_score / 5) * 100, 1) }}%</td>
                </tr>
                <tr class="table-primary">
                  <td colspan="2"><strong>TOTAL</strong></td>
                  <td><strong>{{ number_format($score->total_score, 2) }}</strong></td>
                  <td><strong>100</strong></td>
                  <td><strong>{{ number_format($score->total_score, 1) }}%</strong></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        @if($score->remarks)
        <div class="card mt-3">
          <div class="card-header">
            <h6 class="mb-0">Remarks</h6>
          </div>
          <div class="card-body">
            <p>{{ $score->remarks }}</p>
          </div>
        </div>
        @endif

        <div class="card mt-3">
          <div class="card-header">
            <h6 class="mb-0">Publications ({{ $publications->count() }})</h6>
          </div>
          <div class="card-body">
            @forelse($publications as $pub)
            <div class="border-bottom pb-2 mb-2">
              <strong>{{ $pub->title }}</strong>
              <p class="mb-1 text-muted">{{ $pub->getTypeLabel() }}</p>
              @if($pub->journal_book_name)
              <p class="mb-1"><small>{{ $pub->journal_book_name }}</small></p>
              @endif
              <small class="text-primary">Score: {{ $pub->api_score }}</small>
            </div>
            @empty
            <p class="text-muted">No publications recorded</p>
            @endforelse
          </div>
        </div>

        <div class="card mt-3">
          <div class="card-header">
            <h6 class="mb-0">Activities ({{ $activities->count() }})</h6>
          </div>
          <div class="card-body">
            @forelse($activities as $activity)
            <div class="border-bottom pb-2 mb-2">
              <strong>{{ $activity->activity_name }}</strong>
              <p class="mb-1 text-muted">{{ $activity->getTypeLabel() }}</p>
              @if($activity->role)
              <p class="mb-1"><small>Role: {{ $activity->role }}</small></p>
              @endif
              <small class="text-primary">Score: {{ $activity->api_score }}</small>
            </div>
            @empty
            <p class="text-muted">No activities recorded</p>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')