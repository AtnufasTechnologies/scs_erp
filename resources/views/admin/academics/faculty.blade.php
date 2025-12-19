@include('includes.header')
@include('admin.sidebar')
<h1>Faculty</h1>

<div class="container-fluid card shadow">

  <table class="table mt-3 mb-3 table-hover" id="exportTable">
    <thead>
      <tr>
        <th>#</th>
        <th>EmpID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Gender</th>
        <th>Dob</th>
        <th>Nationality</th>
        <th>Mobile</th>
        <th>Email</th>
        <th></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @if (count($data))
      <?php $sl = 1 ?>
      @foreach ($data as $item)
      <tr>
        <td>{{$sl++}}</td>
        <td><a href="{{url('erp/admin/faculty-profile/'.$item->id)}}" title="View Profile">{{$item->USER_CODE}}</a></td>
        <td>{{$item->FIRST_NAME}}</td>
        <td>{{$item->LAST_NAME}}</td>
        <td>{{$item->GENDER == 1 ? 'Male' : 'Female'}}</td>
        <td>{{date('d-M-Y',strtotime($item->DOB))}}</td>
        <td>{{$item->nationality != null ? $item->nationality->name : ''}}</td>
        <td>{{$item->MOBILE_NO}}</td>
        <td>{{$item->MAIL_ID}}</td>
        <td><button class="badge {{$item->IS_LEFT == 1 ? 'btn-danger': 'btn-success'}} ">{{$item->IS_LEFT == 1 ? 'Left': 'Working'}} </button></td>
        <td>
          <button type="button" class="btn-sm btn-primary "
            data-bs-toggle="modal"
            data-bs-target="#editFacultyModal{{$item->id}}">
            <i class="fa fa-edit"></i>
          </button>
          <div class="modal fade" id="editFacultyModal{{$item->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">{{$item->USER_CODE}}</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{url('erp/admin/update/faculty')}}" method="post">
                  @csrf
                  <div class="modal-body">

                    <div class="row">
                      <div class="col-lg-4">
                        <div class="mb-3">
                          <label class="fw-bold">Employee ID</label>
                          <input type="text" class="form-control" name="empid" value="{{$item->USER_CODE}}">
                        </div>
                      </div>
                      <div class="col-lg-8"></div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label class="fw-bold">First Name</label>
                          <input type="text" class="form-control" name="fname" value="{{$item->FIRST_NAME}}">
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label class="fw-bold">Last Name</label>
                          <input type="text" class="form-control" name="lname" value="{{$item->LAST_NAME}}">
                        </div>
                      </div>
                      <div class="col-lg-3">
                        <div class="mb-3">
                          <label class="fw-bold">DOB</label>
                          <input type="date" class="form-control" name="dob" value="{{date('Y-m-d',strtotime($item->DOB))}}">
                        </div>
                      </div>
                      <div class="col-lg-3">
                        <div class="mb-3">
                          <label class="fw-bold">Gender</label>
                          <select name="gender" class="form-control">
                            <option value="1" {{$item->GENDER == 1 ? 'selected' : ''}}>Male</option>
                            <option value="2" {{$item->GENDER == 2 ? 'selected' : ''}}>Female</option>

                          </select>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label class="fw-bold">Phone</label>
                          <input type="text" class="form-control" name="mobile_no" value="{{$item->MOBILE_NO}}">

                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label class="fw-bold">Email</label>
                          <input type="email" class="form-control" value="{{$item->MAIL_ID}}" name="mail_id">
                        </div>
                      </div>
                    </div>
                    <input type="hidden" name="id" value="{{$item->id}}">
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Update changes</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </td>
      </tr>
      @endforeach
      @else
      <p class="display-4 text-center">No Records Found</p>
      @endif
    </tbody>
  </table>


  @include('includes.footer')