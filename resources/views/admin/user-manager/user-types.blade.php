@include('includes.header')

@include('admin.sidebar')
<h3>User Type </h3>
<button class="cst-button mb-3" style="--clr: #21d9c7ff;" data-bs-toggle="modal" data-bs-target="#add">
  <span class="button-decor"></span>
  <div class="button-content">
    <div class="button__icon">
      <i class="fa fa-plus-circle"></i>
    </div>
    <span class="button__text">Add New</span>
  </div>
</button>

<div class="modal fade" id="add" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">New User Type</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{url('erp/admin/access-control/add-usertype')}}" method="post">
        @csrf
        <div class="modal-body">
          <input type="text" class="form-control mb-3" name="name" placeholder="User Type Name">


        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>

<table class="table table-bordered" id="exportTable">
  <thead>
    <tr>
      <th>#</th>
      <th>Slug</th>
      <th>User Type Name</th>
      <th>Created At</th>
      <th>Updated At</th>
    </tr>
  </thead>

  <tbody>

    @foreach ($data as $key => $dt)
    <tr>
      <td>{{ $key + 1 }}</td>
      <td>{{ $dt->slug }}</td>
      <td>{{ $dt->name }}</td>
      <td>{{ date('d-M-Y', strtotime($dt->created_at)) }}</td>
      <td>{{ date('d-M-Y', strtotime($dt->updated_at)) }}</td>
    </tr>
    @endforeach

  </tbody>


</table>
@include('includes.footer')