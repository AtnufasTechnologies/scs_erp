@include('includes.header')
@include('includes.dept-sidebar')
<div class="main-content">
  <h4>My Specialization Master</h4>
  <div class="alert alert-warning">
    Create your own specializations and combine them with Programs. Applicants get to choose which one they want to go ahead with. Note: it can only be used if status is set to <span class="badge badge-success">Active </span>
  </div>
  <form action="{{route('department.store.specialization')}}" method="post">
    @csrf
    <div class="row">
      <div class="col-lg-4 mb-3">
        <input type="text" name="name" class="form-control" placeholder="Type Title Here...">
      </div>
      <div class="col-lg-2 mb-3">
        <div class="input-group">
          <select name="status" class="form-control">
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
          <input type="hidden" name="subject_id" value="{{$subject->id}}">
          <button type="submit" class="btn btn-success"><i class="fa fa-plus-circle"></i>New</button>
        </div>


      </div>
    </div>
  </form>


  <div class="row">
    @foreach ($data as $item)
    <div class="col-lg-3">
      <div class="card shadow">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <span class="badge {{$item->is_active == 1 ? 'badge-success': 'badge-danger'}}">{{$item->is_active == 1 ? 'Active': 'Inactive'}}</span>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#edit-specialization-{{$item->id}}" aria-expanded="false" aria-controls="edit-specialization-{{$item->id}}">
              Edit
            </button>
          </div>
        </div>
        <div class="card-body">
          <i class="fal fa-stars fa-4x"></i>
          <strong>
            <div class="badge badge-warning"> {{$item->slug}}</div>
          </strong> <br>
          {{$item->name}}
          <div class="collapse mt-3" id="edit-specialization-{{$item->id}}">
            <form action="{{route('department.update.specialization', $item->id)}}" method="post">
              @csrf
              @method('PUT')
              <div class="mb-2">
                <input type="text" name="name" value="{{$item->name}}" class="form-control" placeholder="Type Title Here..." required>
              </div>
              <div class="mb-2">
                <select name="status" class="form-control" required>
                  <option value="1" {{$item->is_active == 1 ? 'selected' : ''}}>Active</option>
                  <option value="0" {{$item->is_active == 0 ? 'selected' : ''}}>Inactive</option>
                </select>
              </div>
              <button type="submit" class="btn btn-warning btn-sm">Update</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    @endforeach
  </div>



</div>

@include('includes.footer')