@include('includes.header')

<div class="wrapper">
  @include('hr.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Faculty Performance Report</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('hr.api-scores.index') }}">API Scores</a></li>
            <li class="breadcrumb-item active">Faculty Report</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-4">
        <div class="card">
          <div class="card-body text-center">
            <h5>{{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}</h5>
            <p class="text-muted">{{ $faculty->USER_CODE }}</p>
            @if($faculty->DEPARTMENT)
            <p class="text-muted"><small>Department ID: {{ $faculty->DEPARTMENT }}</small></p>
            @endif
          </div>
        </div>
      </div>

      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0">Performance Summary</h6>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-md-4">
                <h4>{{ $scores->count() }}</h4>
                <p class="text-muted">Total Sessions</p>
              </div>
              <div class="col-md-4">
                <h4 class="text-success">{{ number_format($scores->avg('total_score'), 2) }}</h4>
                <p class="text-muted">Average Score</p>
              </div>
              <div class="col-md-4">
                <h4 class="text-primary">{{ number_format($scores->max('total_score'), 2) }}</h4>
                <p class="text-muted">Highest Score</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Academic Year-wise Performance</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead class="table-light">
                  <tr>
                    <th>Academic Year</th>
                    <th>Cat I</th>
                    <th>Cat II</th>
                    <th>Cat III</th>
                    <th>Cat IV</th>
                    <th>Cat V</th>
                    <th>Cat VI</th>
                    <th>Cat VII</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($scores as $score)
                  <tr>
                    <td><strong>{{ $score->academicYear->year_name }}</strong></td>
                    <td>{{ number_format($score->category_i_score, 1) }}</td>
                    <td>{{ number_format($score->category_ii_score, 1) }}</td>
                    <td>{{ number_format($score->category_iii_score, 1) }}</td>
                    <td>{{ number_format($score->category_iv_score, 1) }}</td>
                    <td>{{ number_format($score->category_v_score, 1) }}</td>
                    <td>{{ number_format($score->category_vi_score, 1) }}</td>
                    <td>{{ number_format($score->category_vii_score, 1) }}</td>
                    <td><strong class="text-primary">{{ number_format($score->total_score, 2) }}</strong></td>
                    <td>
                      @if($score->status == 'final')
                      <span class="badge bg-success">Final</span>
                      @else
                      <span class="badge bg-warning">Draft</span>
                      @endif
                    </td>
                    <td>
                      <a href="{{ route('hr.api-scores.show', $score->id) }}" class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i> View
                      </a>
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="11" class="text-center">No API scores found for this faculty</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')