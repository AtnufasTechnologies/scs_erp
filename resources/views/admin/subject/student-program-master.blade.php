@include('includes.header')
@include('admin.sidebar')
<h3>Student Program Master</h3>
<!-- Button trigger modal -->
<button class="cst-button mb-3" style="--clr: #21d9c7ff;" data-bs-toggle="modal" data-bs-target="#exampleModal">
  <span class="button-decor"></span>
  <div class="button-content">
    <div class="button__icon">
      <i class="fa fa-plus-circle"></i>
    </div>
    <span class="button__text">Add New</span>
  </div>
</button>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog ">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">New Program</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{route('admin.add.new.student-program')}}" method="post">
        @csrf
        <div class="modal-body">


          <div class="row">
            <div class="col-lg-4">
              <label for=""> Campus *</label>
              <select name="campus" class="form-control" required>
                <option value="" selected>--Select--</option>
                <option value="1">Sonada</option>
                <option value="2">Siliguri Campus</option>
              </select>
            </div>
            <div class="col-lg-4">
              <label for=""> Program Code *</label>
              <input type="text" name="code" class="form-control" required>
            </div>

            <div class="col-lg-4">
              <label for=""> No of Semesters *</label>
              <input type="number" name="semester_count" class="form-control" min="1" required>
            </div>


            <div class="col-lg-12">
              <label for="">Program Name *</label>
              <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <div class="col-lg-12">
              <label for="">Description *</label>
              <textarea name="description" id="description" class="form-control"></textarea>
            </div>


          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="container-fluid">
  <table class="table table-hover" id="exportTable">
    <thead>
      <tr>
        <th>#</th>
        <th>Campus</th>
        <th>Program Code</th>
        <th>Program Name</th>
        <th>Description</th>
        <th>No of Semesters</th>
        <th>Total Enrolled Students</th>
        <th>Edit</th>
      </tr>
    </thead>

    <tbody>
      @if (count($data))

      @foreach ($data as $d)
      <tr>
        <td>{{$loop->iteration}}</td>
        <td>{{$d->campus_id == 1 ? 'Sonada' : 'Siliguri Campus'}}</td>
        <td>{{$d->code}}</td>
        <td><span class="text-capitalize">{{$d->name}}s</span></td>
        <td>{{$d->description}}</td>
        <td>{{$d->semester_count}}</td>
        <td>{{$d->student_count}}</td>
        <td>
          <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#edit{{$d->id}}">
            <i class="fa fa-edit"></i>
          </button>

          <!-- Modal -->
          <div class="modal fade" id="edit{{$d->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog ">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Edit Program</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{route('admin.update.student-program',$d->id)}}" method="post">
                  @csrf
                  <input type="hidden" name="id" value="{{$d->id}}">
                  <div class="modal-body">
                    <div class="row">
                      <div class="col-lg-4">
                        <label for=""> Campus *</label>
                        <select name="campus" class="form-control" required>
                          <option value="" selected>--Select--</option>
                          <option value="1" {{$d->campus_id == 1 ? 'selected' : ''}}>Sonada</option>
                          <option value="2" {{$d->campus_id == 2 ? 'selected' : ''}}>Siliguri Campus</option>
                        </select>
                      </div>
                      <div class="col-lg-4">
                        <label for=""> Program Code *</label>
                        <input type="text" name="code" value="{{$d->code}}" class="form-control" required>
                      </div>

                      <div class="col-lg-4">
                        <label for=""> No of Semesters *</label>
                        <input type="number" name="semester_count" value="{{$d->semester_count}}" class="form-control" min="1" required>
                      </div>
                      <div class="col-lg-12">
                        <label for="">Program Name *</label>
                        <input type="text" name="name" value="{{$d->name}}" class="form-control" required>
                      </div>
                      <div class="col-lg-12">
                        <label for="">Description *</label>
                        <textarea name="description" class="form-control" required>{{$d->description}}</textarea>
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </td>
      </tr>
      @endforeach
      @else
      <tr>
        <td colspan="6" class="text-center">No data found</td>
      </tr>
      @endif
    </tbody>
  </table>
</div>
@include('includes.footer')