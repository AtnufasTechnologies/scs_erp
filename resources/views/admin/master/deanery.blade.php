@include('includes.header')
@include('admin.sidebar')

<h3><span class="text-uppercase">Deanery</span></h3>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  {{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  {{ session('error') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger">
  <ul class="mb-0 ps-3">
    @foreach($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif

<button class="cst-button mb-3" style="--clr: #21d9c7ff;" data-bs-toggle="modal" data-bs-target="#addDeaneryModal">
  <span class="button-decor"></span>
  <div class="button-content">
    <div class="button__icon">
      <i class="fa fa-plus-circle"></i>
    </div>
    <span class="button__text">Add Deanery</span>
  </div>
</button>

<div class="modal fade" id="addDeaneryModal" tabindex="-1" aria-labelledby="addDeaneryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addDeaneryModalLabel">Add Deanery</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{url('erp/admin/master/deanery')}}" method="post">
        @csrf
        <div class="modal-body">
          <label class="form-label">Select Campus *</label>
          <select name="campus_id" class="form-control mb-3" required>
            <option value="">Select Campus</option>
            @foreach ($campuses as $campus)
            <option value="{{$campus->id}}">{{$campus->name}}</option>
            @endforeach
          </select>


          <label class="form-label">Deanery Name *</label>
          <input type="text" class="form-control mb-3" name="title" placeholder="Type Here..." required>

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>


<div class="container-fluid card shadow">

  <table class="table mt-3 mb-3" id="exportTable">
    <thead>
      <tr>
        <th>#</th>
        <th>Name</th>
        <th>Campus</th>
        <th>Departments</th>
        <th></th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @if (count($deanery))
      <?php $sl = 1 ?>
      @foreach($deanery as $d)
      @php
      $mappedDeptIds = $d->deanerydeptpivot->pluck('dept_id')->map(fn($v) => (int) $v)->all();
      @endphp
      <tr>
        <td>{{$sl++}}</td>
        <td>{{ $d->title }}</td>
        <td>{{ $d->campus->name ?? ($d->program->campus->name ?? '') }}</td>
        <td>
          <div class="d-flex flex-wrap gap-2 mb-2">
            @foreach($d->deanerydeptpivot as $dp)
            <span class="badge bg-primary">
              {{$dp->department != null ? ($dp->department->title ?? $dp->department->name ?? 'N/A') : 'N/A'}}
              <a
                href="{{ route('deanery.departments.delete', $dp->id) }}"
                onclick="return confirm('Remove this subject/department from deanery?')"
                class="text-white text-decoration-none ms-1"
                title="Remove Mapping">x</a>
            </span>
            @endforeach
          </div>
        </td>
        <td>
          <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#addDepartmentsModal{{$d->id}}">
            <i class="fa fa-plus-circle"></i> DEPT
          </button>



          <div class="modal fade" id="addDepartmentsModal{{$d->id}}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Add Departments - {{$d->title}}</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('deanery.departments.store', $d->id) }}">
                  @csrf
                  <div class="modal-body">
                    <label class="form-label">Select Departments</label>
                    <select name="dept_ids[]" class="dselect-example" multiple size="10" required>
                      @foreach($departments as $department)
                      @if((int) ($department->campus_id ?? 0) === (int) ($d->campus_id ?? 0))
                      <option value="{{ $department->id }}">{{ $department->title }}</option>
                      @endif
                      @endforeach
                    </select>

                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Add</button>
                  </div>
                </form>
              </div>
            </div>
          </div>


        </td>
        <td>
          <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editDeaneryModal{{$d->id}}">
            <i class="fa fa-edit"></i>
          </button>
          <a
            href="{{ route('deanery.delete', $d->id) }}"
            onclick="return confirm('Delete deanery and all mapped subjects/departments?')"
            class="btn btn-sm btn-danger">
            <i class="fa fa-trash"></i>
          </a>

          <div class="modal fade" id="editDeaneryModal{{$d->id}}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Edit Deanery</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('deanery.update', $d->id) }}">
                  @csrf
                  <div class="modal-body">
                    <label class="form-label">Select Campus *</label>
                    <select name="campus_id" class="form-control mb-3" required>
                      @foreach($campuses as $campus)
                      <option value="{{ $campus->id }}" {{ (int) ($d->campus_id ?? 0) === (int) $campus->id ? 'selected' : '' }}>
                        {{ $campus->name }}
                      </option>
                      @endforeach
                    </select>



                    <label class="form-label">Deanery Name *</label>
                    <input type="text" name="title" class="form-control" value="{{ $d->title }}" required>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Update</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </td>


      </tr>
      @endforeach

      @else
      <p class="display-4 text-center">No Records</p>
      @endif
    </tbody>

  </table>
</div>


@include('includes.footer')