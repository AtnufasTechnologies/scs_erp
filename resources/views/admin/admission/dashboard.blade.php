@include('includes.header')
@include('admin.admission.sidebar')

<h3 class="mb-1"><i class="fas fa-door-open text-primary me-2"></i>Admission Dashboard</h3>
<p class="text-muted small mb-4">{{ now()->format('d M Y') }}</p>

{{-- ===== STAT CARDS ===== --}}
<div class="row g-3 mb-4">

  <div class="col-sm-6 col-xl-3">
    <div class="card radius-10 border-start border-4 border-primary h-100">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div>
            <p class="mb-0 text-secondary">UG Registrations</p>
            <h4 class="my-1 text-primary">{{ number_format($totalUgRegistrations) }}</h4>
            <p class="mb-0 text-secondary small">Today: <strong>+{{ $todayUgRegistrations }}</strong></p>
          </div>
          <div class="ms-auto widget-icon bg-primary text-white">
            <i class="fas fa-certificate"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card radius-10 border-start border-4 border-info h-100">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div>
            <p class="mb-0 text-secondary">PG Registrations</p>
            <h4 class="my-1 text-info">{{ number_format($totalPgRegistrations) }}</h4>
            <p class="mb-0 text-secondary small">Today: <strong>+{{ $todayPgRegistrations }}</strong></p>
          </div>
          <div class="ms-auto widget-icon bg-info text-white">
            <i class="fas fa-badge"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card radius-10 border-start border-4 border-warning h-100">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div>
            <p class="mb-0 text-secondary">Applications Submitted</p>
            <h4 class="my-1 text-warning">{{ number_format($totalUgApplications + $totalPgApplications) }}</h4>
            <p class="mb-0 text-secondary small">UG: {{ $totalUgApplications }} &nbsp;|&nbsp; PG: {{ $totalPgApplications }}</p>
          </div>
          <div class="ms-auto widget-icon bg-warning text-white">
            <i class="fas fa-file-alt"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card radius-10 border-start border-4 border-success h-100">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div>
            <p class="mb-0 text-secondary">App. Fee Collected</p>
            <h4 class="my-1 text-success">₹ {{ number_format($totalAppFeeCollected, 2) }}</h4>
            <p class="mb-0 text-secondary small">Today: ₹ {{ number_format($todayAppFeeCollected, 2) }}</p>
          </div>
          <div class="ms-auto widget-icon bg-success text-white">
            <i class="fas fa-rupee-sign"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

{{-- ===== SELECTION PIPELINE ===== --}}
<div class="row g-3 mb-4">

  <div class="col-sm-4">
    <div class="card radius-10 border-start border-4 border-secondary text-center h-100">
      <div class="card-body">
        <i class="fas fa-clipboard-list fa-2x text-secondary mb-2"></i>
        <p class="mb-0 text-secondary">Phase 1 Selections</p>
        <h3 class="my-1">{{ number_format($phase1Count) }}</h3>
        <a href="{{ route('admission.ug.phase1') }}" class="btn btn-sm btn-outline-secondary mt-2">View</a>
      </div>
    </div>
  </div>

  <div class="col-sm-4">
    <div class="card radius-10 border-start border-4 border-purple text-center h-100">
      <div class="card-body">
        <i class="fas fa-check-double fa-2x text-primary mb-2"></i>
        <p class="mb-0 text-secondary">Phase 2 (Final) Selections</p>
        <h3 class="my-1">{{ number_format($phase2Count) }}</h3>
        <a href="{{ route('admission.ug.phase2') }}" class="btn btn-sm btn-outline-primary mt-2">View</a>
      </div>
    </div>
  </div>

  <div class="col-sm-4">
    <div class="card radius-10 border-start border-4 border-success text-center h-100">
      <div class="card-body">
        <i class="fas fa-user-graduate fa-2x text-success mb-2"></i>
        <p class="mb-0 text-secondary">Enrolled Students</p>
        <h3 class="my-1 text-success">{{ number_format($enrolledCount) }}</h3>
        <span class="badge bg-success mt-2">Confirmed Enrollments</span>
      </div>
    </div>
  </div>

</div>

{{-- ===== TREND CHART + RECENT REGISTRATIONS ===== --}}
<div class="row g-3">

  {{-- Registration Trend Chart --}}
  <div class="col-xl-5">
    <div class="card shadow-sm h-100">
      <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-chart-line me-1 text-primary"></i> Registrations – Last 14 Days</h6>
      </div>
      <div class="card-body">
        <canvas id="regTrendChart" height="220"></canvas>
      </div>
    </div>
  </div>

  {{-- Recent Registrations Table --}}
  <div class="col-xl-7">
    <div class="card shadow-sm h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-list me-1"></i> Recent Registrations</h6>
        <div class="d-flex gap-2">
          <a href="{{ route('admission.registration', ['type' => 'UG']) }}" class="btn btn-sm btn-outline-primary">UG</a>
          <a href="{{ route('admission.registration', ['type' => 'PG']) }}" class="btn btn-sm btn-outline-info">PG</a>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Type</th>
                <th>Campus</th>
                <th>Mobile</th>
                <th>Registered</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentRegistrations as $idx => $reg)
              <tr>
                <td>{{ $idx + 1 }}</td>
                <td class="text-capitalize">{{ $reg->first_name }} {{ $reg->last_name }}</td>
                <td>
                  <span class="badge {{ $reg->application_type === 'UG' ? 'bg-primary' : 'bg-info' }}">
                    {{ $reg->application_type }}
                  </span>
                </td>
                <td>{{ $reg->campusmaster->name ?? '-' }}</td>
                <td>{{ $reg->mobile_no }}</td>
                <td>{{ $reg->created_at->format('d M Y') }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center text-muted py-3">No registrations found</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</div>

{{-- ===== QUICK ACTIONS ===== --}}
<div class="card shadow-sm mt-4">
  <div class="card-header bg-light">
    <h6 class="mb-0"><i class="fas fa-bolt text-warning me-1"></i> Quick Actions</h6>
  </div>
  <div class="card-body d-flex flex-wrap gap-2">
    <a href="{{ route('admission.registration', ['type' => 'UG']) }}" class="btn btn-outline-primary">
      <i class="fas fa-certificate me-1"></i> UG Registrations
    </a>
    <a href="{{ route('admission.registration', ['type' => 'PG']) }}" class="btn btn-outline-info">
      <i class="fas fa-badge me-1"></i> PG Registrations
    </a>
    <a href="{{ route('admission.ug.applications') }}" class="btn btn-outline-warning">
      <i class="fas fa-file-alt me-1"></i> UG Applications
    </a>
    <a href="{{ route('admission.pg.applications') }}" class="btn btn-outline-secondary">
      <i class="fas fa-file-alt me-1"></i> PG Applications
    </a>
    <a href="{{ route('admission.ug.phase1') }}" class="btn btn-outline-dark">
      <i class="fas fa-clipboard-list me-1"></i> Phase 1
    </a>
    <a href="{{ route('admission.ug.phase2') }}" class="btn btn-outline-dark">
      <i class="fas fa-check-double me-1"></i> Phase 2
    </a>
    <a href="{{ route('admin.accounts.admission-application-fee') }}" class="btn btn-outline-success">
      <i class="fas fa-rupee-sign me-1"></i> Application Fees
    </a>
    <a href="{{ route('admission.settings') }}" class="btn btn-outline-danger">
      <i class="fas fa-cog me-1"></i> Settings
    </a>
  </div>
</div>

{{-- Chart.js --}}
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const rawTrend = @json($regTrend);

    // Build date labels for last 14 days
    const labels = [];
    for (let i = 13; i >= 0; i--) {
      const d = new Date();
      d.setDate(d.getDate() - i);
      labels.push(d.toISOString().slice(0, 10));
    }

    const ugData = labels.map(date => {
      const found = rawTrend.find(r => r.date === date && r.application_type === 'UG');
      return found ? found.count : 0;
    });
    const pgData = labels.map(date => {
      const found = rawTrend.find(r => r.date === date && r.application_type === 'PG');
      return found ? found.count : 0;
    });

    const shortLabels = labels.map(d => {
      const dt = new Date(d);
      return dt.toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'short'
      });
    });

    new Chart(document.getElementById('regTrendChart'), {
      type: 'bar',
      data: {
        labels: shortLabels,
        datasets: [{
            label: 'UG',
            data: ugData,
            backgroundColor: 'rgba(13, 110, 253, 0.7)',
            borderRadius: 4,
          },
          {
            label: 'PG',
            data: pgData,
            backgroundColor: 'rgba(13, 202, 240, 0.7)',
            borderRadius: 4,
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top'
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });
  });
</script>

@include('includes.footer')