@include('includes.header')

<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Students</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Student Master</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="card mt-3">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Student Master - All Campuses</h5>
        <form method="GET" action="{{ route('principal.students.index') }}" class="d-flex align-items-center gap-2">
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
          <table class="table table-hover table-striped" id="exportTable">
            <thead class="bg-dark text-light">
              <tr>
                <th>#</th>
                <th>Reg No</th>
                <th>Roll No</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>DOB</th>
                <th>Gender</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Religion</th>
                <th>Campus</th>
                <th>Batch</th>
                <th>Department</th>
                <th>Program</th>
                <th>Current Year</th>
              </tr>
            </thead>
            <tbody>
              @if(count($data))
              @php $sl = 1; @endphp
              @foreach($data as $item)
              <tr>
                <td>{{ $sl++ }}</td>
                <td>{{ $item->register_no }}</td>
                <td>
                  <a href="{{ route('principal.student.profile', ['id' => $item->id, 'rollno' => $item->roll_no]) }}" class="text-uppercase btn-sm btn-success">{{ $item->roll_no }}</a>
                </td>
                <td class="text-capitalize">{{ $item->first_name }}</td>
                <td class="text-capitalize">{{ $item->last_name }}</td>
                <td>{{ $item->dob }}</td>
                <td>{{ $item->gender == '1' ? 'Male' : 'Female' }}</td>
                <td><a href="mailto:{{ $item->mail_id }}">{{ $item->mail_id }}</a></td>
                <td>{{ $item->mobile_no }}</td>
                <td class="text-capitalize">{{ $item->religionmaster != null ? $item->religionmaster->name : '' }}</td>
                <td>{{ $item->campusmaster != null ? $item->campusmaster->name : '' }}</td>
                <td>{{ $item->batchmaster != null ? $item->batchmaster->batch_name : '' }}</td>
                <td>{{ $item->deptmaster != null ? $item->deptmaster->name : '' }}</td>
                <td>{{ $item->programgroup != null ? $item->programgroup->program_code : '' }}</td>
                <td>{{ $item->current_year }}</td>
              </tr>
              @endforeach
              @else
              <tr>
                <td colspan="15" class="text-center py-4">No Records Found</td>
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