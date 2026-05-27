<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Current Vacancies | {{ config('app.name') }}</title>
  <link href="{{ asset('admin/css/bootstrap.min.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      background: #f8f9fa;
      font-family: 'Roboto', sans-serif;
    }

    .navbar-brand img {
      height: 45px;
    }

    .vacancy-card {
      transition: box-shadow .2s;
    }

    .vacancy-card:hover {
      box-shadow: 0 4px 20px rgba(0, 0, 0, .12);
    }

    .badge-type {
      font-size: .75rem;
    }

    footer {
      background: #2c3e50;
      color: #adb5bd;
    }
  </style>
</head>

<body>

  {{-- Navbar --}}
  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center gap-2" href="/">
        <img src="{{ asset('admin/images/logo.png') }}" alt="Logo">
        <span class="fw-bold text-dark">{{ config('app.name') }}</span>
      </a>
      <div class="ms-auto">
        <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
          <i class="fas fa-sign-in-alt me-1"></i>Login
        </a>
      </div>
    </div>
  </nav>

  {{-- Hero --}}
  <div class="bg-primary text-white py-5">
    <div class="container">
      <h1 class="fw-bold mb-1"><i class="fas fa-briefcase me-2"></i>Current Vacancies</h1>
      <p class="mb-0 opacity-75">Join our team — explore open positions and apply online.</p>
    </div>
  </div>

  <div class="container py-5">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
      <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($vacancies->isEmpty())
    <div class="text-center py-5">
      <i class="fas fa-folder-open fa-4x text-muted mb-3 d-block"></i>
      <h4 class="text-muted">No open vacancies at this time.</h4>
      <p class="text-muted">Please check back later for new opportunities.</p>
    </div>
    @else

    <div class="row mb-4">
      <div class="col-md-6">
        <p class="text-muted mb-0"><strong>{{ $vacancies->count() }}</strong> position(s) currently open</p>
      </div>
    </div>

    <div class="row g-4">
      @foreach($vacancies as $vacancy)
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 vacancy-card border-0 shadow-sm">
          <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <span class="badge bg-primary badge-type">{{ ucfirst(str_replace('-', ' ', $vacancy->recruitment_type)) }}</span>
              <span class="badge bg-{{ $vacancy->application_end_date >= now() ? 'success' : 'danger' }}">
                {{ $vacancy->application_end_date >= now() ? 'Open' : 'Closing Soon' }}
              </span>
            </div>
            <h5 class="card-title mb-1">{{ $vacancy->position_title }}</h5>
            @if($vacancy->department)
            <p class="text-muted small mb-2"><i class="fas fa-building me-1"></i>{{ $vacancy->department->subject_name }}</p>
            @endif
            <div class="d-flex gap-3 mb-3 text-muted small">
              <span><i class="fas fa-briefcase me-1"></i>{{ ucfirst(str_replace('-', ' ', $vacancy->employment_type)) }}</span>
              <span><i class="fas fa-users me-1"></i>{{ $vacancy->number_of_positions }} post(s)</span>
            </div>
            @if($vacancy->salary_range)
            <p class="text-muted small mb-2"><i class="fas fa-rupee-sign me-1"></i>{{ $vacancy->salary_range }}</p>
            @endif
            <div class="mt-auto">
              <div class="d-flex justify-content-between align-items-center text-muted small mb-3">
                <span><i class="fas fa-calendar-alt me-1"></i>Apply by: <strong>{{ \Carbon\Carbon::parse($vacancy->application_end_date)->format('d M Y') }}</strong></span>
              </div>
              <div class="d-flex gap-2">
                <a href="{{ route('vacancies.public.show', $vacancy->id) }}" class="btn btn-outline-primary btn-sm flex-fill">
                  <i class="fas fa-eye me-1"></i>View Details
                </a>
                <a href="{{ route('vacancies.public.apply-form', $vacancy->id) }}" class="btn btn-primary btn-sm flex-fill">
                  <i class="fas fa-paper-plane me-1"></i>Apply
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
    @endif
  </div>

  <footer class="py-4 mt-5">
    <div class="container text-center">
      <p class="mb-0 small">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
  </footer>

  <script src="{{ asset('admin/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>