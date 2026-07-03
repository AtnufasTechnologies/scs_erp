@include('includes.header')

<div class="wrapper">
  @include('hr.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Employee Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Employee List</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card">
      <div class="card-header bg-transparent">
        <div class="row align-items-center">
          <div class="col-md-6">
            <h5 class="mb-0">Master List</h5>
          </div>
          <div class="col-md-6 text-end">
            <a href="{{ route('hr.faculty.create') }}" class="btn btn-primary">
              <i class="fas fa-plus me-1"></i>Add Employee
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <!-- Search and Filter -->
        <form method="GET" action="{{ route('hr.faculty.index') }}" class="mb-4">
          <div class="row g-3">
            <div class="col-md-6">
              <input type="text" name="search" class="form-control" placeholder="Search by name, code, email, or phone" value="{{ $search }}">
            </div>

            <div class="col-md-3">
              <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-search me-1"></i>Search
              </button>
            </div>
          </div>
        </form>

        <!-- Faculty Table -->
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>Employee Code</th>
                <th>Campus </th>
                <th>Name</th>
                <th>Contact</th>
                <th>Employeement Type</th>
                <th>Designation</th>
                <th>Status</th>
                <th>HR Remark</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($faculties as $faculty)
              <tr>
                <td><strong>{{ $faculty->USER_CODE }}</strong></td>
                <td>{{ $faculty->CAMPUS_ID == 1 ? 'Sonada' : 'Siliguri' }}</td>
                <td>{{ $faculty->FIRST_NAME }} {{ $faculty->MIDDLE_NAME }} {{ $faculty->LAST_NAME }}
                </td>
                <td>
                  <small>{{ $faculty->MAIL_ID }}</small><br>
                  <small>{{ $faculty->MOBILE_NO }}</small>
                </td>
                <td>{{ $faculty->employee_type ?? 'N/A' }}</td>
                <td>{{ $faculty->designation ?? 'N/A' }}</td>
                <td>
                  @if($faculty->IS_LEFT == 1)
                  <span class="badge bg-danger">Left - {{$faculty->DOL != null  ? date('d-M-Y', strtotime($faculty->DOL)) : 'Missing Info'}}</span>
                  @else
                  <span class="badge bg-success">Active Since {{$faculty->DOJ != null ? date('d-M-Y', strtotime($faculty->DOJ)) : '-'}} </span>
                  @endif
                </td>
                <td>{{ $faculty->hr_remark ? \Illuminate\Support\Str::limit($faculty->hr_remark, 40) : '-' }}</td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <a href="{{ route('hr.faculty.show', $faculty->id) }}" class="btn btn-outline-primary mx-2" title="View">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('hr.faculty.edit', $faculty->id) }}" class="btn btn-outline-secondary mx-2" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    @if($faculty->IS_LEFT == 1)
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#reactivateModal"
                      data-id="{{ $faculty->id }}"
                      data-emp_code="{{ $faculty->USER_CODE }}"
                      data-name="{{ $faculty->FIRST_NAME }} {{ $faculty->MIDDLE_NAME }} {{ $faculty->LAST_NAME }}"
                      data-email="{{ $faculty->MAIL_ID }}"
                      data-doj="{{ $faculty->DOJ ? date('Y-m-d', strtotime($faculty->DOJ)) : '' }}"
                      data-hr_remark="{{ $faculty->hr_remark }}">
                      Reactivate
                    </button>
                    @else
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deactivateModal"
                      data-id="{{ $faculty->id }}"
                      data-emp_code="{{ $faculty->USER_CODE }}"
                      data-name="{{ $faculty->FIRST_NAME }} {{ $faculty->MIDDLE_NAME }} {{ $faculty->LAST_NAME }}"
                      data-email="{{ $faculty->MAIL_ID }}"
                      data-dol="{{ $faculty->DOL ? date('Y-m-d', strtotime($faculty->DOL)) : '' }}"
                      data-hr_remark="{{ $faculty->hr_remark }}">
                      Deactivate
                    </button>
                    @endif


                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="9" class="text-center text-muted">No faculty members found</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
          {{ $faculties->links('vendor.pagination.bootstrap-5') }}
        </div>
      </div>
    </div>

  </main>
  <!--end main wrapper-->
</div>
<!-- Modal -->
<div class="modal fade" id="deactivateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Deactivate Staff # <span id="emp_code"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{route('hr.faculty.left')}}" method="post">
        @csrf
        <div class="modal-body">
          <input type="hidden" name="id" id="id">
          <label>Name</label>
          <input type="text" name="name" id="user_name" class="form-control mb-2" readonly>
          <label>Email</label>
          <input type="email" name="email" id="user_email" class="form-control mb-2" readonly>
          <label>Resignation Date <span class="text-danger">*</span></label>
          <input type="date" name="resignation_date" id="resignation_date" class="form-control mb-2">
          <label>HR Remark</label>
          <textarea name="hr_remark" id="hr_remark" class="form-control mb-2" rows="2" placeholder="Optional note for resignation/deactivation"></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="reactivateModal" tabindex="-1" aria-labelledby="reactivateModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reactivateModalLabel">Reactivate Staff # <span id="reactivate_emp_code"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="" method="post" id="reactivateForm">
        @csrf
        <div class="modal-body">
          <label>Name</label>
          <input type="text" id="reactivate_user_name" class="form-control mb-2" readonly>
          <label>Email</label>
          <input type="email" id="reactivate_user_email" class="form-control mb-2" readonly>
          <label>Rejoining Date (DOJ) <span class="text-danger">*</span></label>
          <input type="date" name="DOJ" id="reactivation_date" class="form-control mb-2" required>
          <label>HR Remark</label>
          <textarea name="hr_remark" id="reactivate_hr_remark" class="form-control mb-2" rows="2" placeholder="Optional note for rejoin/reactivation"></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>
@include('includes.footer')
<script>
  const restoreRouteTemplate = "{{ route('hr.faculty.restore', ['id' => '__ID__']) }}";

  const editModal = document.getElementById('deactivateModal');
  editModal.addEventListener('show.bs.modal', function(event) {
    // button which opened modal
    let button = event.relatedTarget;
    // get data attributes
    let id = button.getAttribute('data-id');
    let name = button.getAttribute('data-name');
    let email = button.getAttribute('data-email');
    let user_code = button.getAttribute('data-emp_code');
    let dol = button.getAttribute('data-dol');
    let hr_remark = button.getAttribute('data-hr_remark');
    // fill modal inputs

    document.getElementById('id').value = id;
    document.getElementById('user_name').value = name;
    document.getElementById('emp_code').textContent = user_code;
    document.getElementById('user_email').value = email;
    document.getElementById('resignation_date').value = dol;
    document.getElementById('hr_remark').value = hr_remark ?? '';


  });

  const reactivateModal = document.getElementById('reactivateModal');
  reactivateModal.addEventListener('show.bs.modal', function(event) {
    let button = event.relatedTarget;
    let id = button.getAttribute('data-id');
    let name = button.getAttribute('data-name');
    let email = button.getAttribute('data-email');
    let user_code = button.getAttribute('data-emp_code');
    let doj = button.getAttribute('data-doj');
    let hr_remark = button.getAttribute('data-hr_remark');

    document.getElementById('reactivate_emp_code').textContent = user_code;
    document.getElementById('reactivate_user_name').value = name;
    document.getElementById('reactivate_user_email').value = email;
    document.getElementById('reactivation_date').value = doj ?? '';
    document.getElementById('reactivate_hr_remark').value = hr_remark ?? '';
    document.getElementById('reactivateForm').action = restoreRouteTemplate.replace('__ID__', id);
  });
</script>