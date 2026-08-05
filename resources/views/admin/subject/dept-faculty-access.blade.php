@include('includes.header')
@include('includes.dept-sidebar')
<!-- Main Content -->
<div class="main-content">
  @if ($errors->any())

  <div class="alert alert-warning alert-dismissible fade show" role="alert">
    <ul>
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>

  @endif
  <div class="container-fluid py-4">
    <nav class="navbar navbar-expand-lg navbar-dark mb-4" style="background: linear-gradient(135deg, #5740b4 0%, #8931f6 100%); border-radius: 0.75rem;">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="#">
          <img src="{{ asset('admin/images/logo.png') }}" alt="Logo" style="max-height: 50px;" class="me-2">
          <span class="fw-bold text-white text-capitalize">{{ $departmentSlug ?? '-' }} / Faculty Access</span>
        </a>
        <div class="d-flex">
          <a href="{{ route('department.dashboard') }}" class="btn btn-light btn-sm fw-bold ms-auto" style="box-shadow:0 2px 8px #0002;">
            <i class="fa fa-step-backward me-1"></i> back
          </a>
        </div>
      </div>
    </nav>
  </div>
  <!-- Button to trigger modal for new objective -->
  <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addObjectiveModal">
    <i class="fa fa-plus-circle"></i> New Access
  </button>
  <!-- Modal for adding new objective -->
  <div class="modal fade" id="addObjectiveModal" tabindex="-1" aria-labelledby="addObjectiveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addObjectiveModalLabel">Add New Access</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('department.faculty.grant-access') ?? '#' }}" method="post">
          @csrf
          <div class="modal-body">
            <div class="row">

              <div class="col-lg-6">
                <div class="mb-3">
                  <label for="lecturesNeeded" class="form-label">Select Your Faculty</label>
                  <select name="faculty_id" id="faculty_id" class="form-select">
                    <option value="" selected disabled>Select </option>
                    @foreach($faculties as $item)
                    <option value="{{ $item->faculty->id }}">{{ $item->faculty->FIRST_NAME }} {{ $item->faculty->LAST_NAME }}</option>
                    @endforeach
                  </select>
                </div>

              </div>
              <div class="col-lg-6">
                <div class="mb-3">
                  <label for="password" class="form-label">Set Default Password (Min : 6 characters)*</label>
                  <input type="text" class="form-control" id="password" name="password" placeholder="Enter Password" required>
                </div>
              </div>


              <input type="hidden" name="subject_id" value="{{ $departmentId }}">
            </div>
            <div class="modal-footer">

              <button type="submit" class="btn btn-success">Create Access</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </form>
      </div>
    </div>

  </div>

  <div class="container-fluid">
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <h5 class="card-title fw-bold mb-4">Faculty Access List</h5>
        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle" id="myTable">
            <thead class="table-light">
              <tr>
                <th scope="col">#</th>
                <th scope="col">Faculty Name</th>
                <th scope="col">Email</th>
                <th scope="col">Phone</th>
                <th scope="col">Default Password</th>
                <th scope="col">Status</th>
                <th scope="col">Access Granted On</th>
                <th scope="col" style="width: 150px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($data as $item)
              <tr>
                <td>{{ $loop->iteration}}</td>
                <td>{{ $item->useraccess->name  ?? '-'}} </td>
                <td>{{ $item->useraccess->email ?? '-'}}</td>
                <td>{{ $item->useraccess->phone ?? '-' }}</td>
                <td>{{ $item->useraccess->decrypted_password ?? '-' }}</td>
                <td>{{ $item->useraccess->status == 'ACTIVE' ? 'Active' : 'Inactive' }}</td>
                <td>{{ $item->useraccess->created_at ? $item->useraccess->created_at->format('d M Y') : '-' }}</td>

                <td>
                  @if ($item->useraccess->status == 'ACTIVE')
                  <a href="{{ route('department.faculty.revoke-access', $item->access_id) }}" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to revoke access for this faculty?');">
                    Revoke Access
                  </a>@else
                  <a href="{{ route('department.faculty.revoke-access', $item->access_id) }}" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to activate access for this faculty?');">
                    Activate Access
                  </a>
                  @endif

                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center">No faculty access granted yet.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

  @include('includes.footer')