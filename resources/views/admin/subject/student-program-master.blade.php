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
        <th>Total Enrolled Students</th>
      </tr>
    </thead>

    <tbody>
      @if (count($data))
      <?php $sl = 1 ?>
      @foreach ($data as $d)
      <tr>
        <td>{{$sl++}}</td>
        <td>{{$d['campus'] == 1 ? 'Sonada' : 'Siliguri Campus'}}</td>
        <td>{{$d['program_code']}}</td>
        <td>{{$d['program_name']}}</td>
        <td>{{$d['program_description']}}</td>
        <td>{{$d['student_count']}}</td>
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