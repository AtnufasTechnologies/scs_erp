@include('includes.header')

<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Admissions</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Admissions</li>
          </ol>
        </nav>
      </div>
    </div>
    <a href="{{route('admission.ug.phase1')}}"><button class="btn btn-success mb-3">UG Interview</button></a>
    <a href="{{route('admission.pg.phase1')}}"><button class="btn btn-success mb-3">PG Interview</button></a>
    <!--summary stats-->
    <div class="row mt-3">


      @foreach($campuses as $campus)
      <div class="col-xl-6 col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white">
            <h6 class="mb-0"><i class="fas fa-building me-2"></i>{{ $campus->name }}</h6>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-6">
                <h4 class="fw-bold text-primary">{{ $regByCampus[$campus->id] ?? 0 }}</h4>
                <p class="text-muted mb-0">Registrations</p>
              </div>
              <div class="col-6">
                <h4 class="fw-bold text-success">{{ $appByCampus[$campus->id] ?? 0 }}</h4>
                <p class="text-muted mb-0">Applications</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      @endforeach
    </div>

    <!--filter-->
    <div class="card mt-3">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Admission Registrations</h5>
        <form method="GET" action="{{ route('principal.admissions.index') }}" class="d-flex align-items-center gap-2">
          <select name="campus_id" class="form-select form-select-sm" style="width: 200px;" onchange="this.form.submit()">
            <option value="">All Campuses</option>
            @foreach($campuses as $campus)
            <option value="{{ $campus->id }}" {{ (string)$selectedCampus === (string)$campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
            @endforeach
          </select>
        </form>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover table-striped table-sm" id="myTable">
            <thead class="bg-dark text-light">
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Campus</th>
                <th>Application Type</th>
                <th>Status</th>
                <th>Registered At</th>
              </tr>
            </thead>
            <tbody>
              @if(count($registrations))
              @php $sl = 1; @endphp
              @foreach($registrations as $reg)
              <tr>
                <td>{{ $sl++ }}</td>
                <td class="text-capitalize">{{ $reg->first_name }} {{ $reg->last_name }}</td>
                <td><a href="mailto:{{ $reg->mail_id }}">{{ $reg->mail_id }}</a></td>
                <td>{{ $reg->mobile_no }}</td>
                <td>{{ $reg->campusmaster ? $reg->campusmaster->name : '-' }}</td>
                <td><span class="badge bg-info">{{ $reg->application_type }}</span></td>
                <td>
                  @if($reg->application_status == 1)
                  <span class="badge bg-success">Active</span>
                  @else
                  <span class="badge bg-secondary">Pending</span>
                  @endif
                </td>
                <td>{{ $reg->created_at ? $reg->created_at->format('d M Y') : '-' }}</td>
              </tr>
              @endforeach
              @else
              <tr>
                <td colspan="8" class="text-center py-4">No Registrations Found</td>
              </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!--applications-->
    <div class="card mt-3 mb-4">


      <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Admission Applications</h5>

      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover table-striped table-sm" id="exportTable">
            <thead class="bg-dark text-light">
              <tr>
                <th>#</th>
                <th>Application Code</th>
                <th>Applicant Name</th>
                <th>Campus</th>
                <th>Program</th>
                <th>Payment Status</th>

                <th>Applied At</th>
              </tr>
            </thead>
            <tbody>
              @if(count($applications))
              @php $sl = 1; @endphp
              @foreach($applications as $app)
              <tr>
                <td>{{ $sl++ }}</td>
                <td>{{ $app->application_code ?? '-' }}</td>
                <td class="text-capitalize">
                  {{ $app->registrationmaster ? $app->registrationmaster->first_name . ' ' . $app->registrationmaster->last_name : '-' }}
                </td>
                <td>
                  {{ $app->registrationmaster && $app->registrationmaster->campusmaster ? $app->registrationmaster->campusmaster->name : '-' }}
                </td>

                <td>{{ $app->stdCourseMaster && $app->stdCourseMaster->name ? $app->stdCourseMaster->name : '-' }}</td>

                <td>
                  @if($app->payment_gateway_status == 'success')
                  <span class="badge bg-success">Paid</span>
                  @elseif($app->payment_gateway_status == 'pending')
                  <span class="badge bg-warning">Pending</span>
                  @elseif($app->payment_gateway_status == 'failed')
                  <span class="badge bg-danger">Failed</span>
                  @else
                  <span class="badge bg-secondary">{{ $app->payment_gateway_status ?? 'N/A' }}</span>
                  @endif
                </td>
                <td>{{ $app->created_at ? $app->created_at->format('d M Y') : '-' }}</td>
              </tr>
              @endforeach
              @else
              <tr>
                <td colspan="8" class="text-center py-4">No Applications Found</td>
              </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </main>
</div>

@include('includes.footer')