@include('includes.header')
@include('admin.sidebar')

<h3><span class="text-uppercase">Cognitive Level</span></h3>
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
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">New </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{url('erp/admin/master/cognitive-lvl')}}" method="post">
        @csrf
        <div class="modal-body">
          <div class="row">
            <div class="col-lg-4"><label for="">Short Name *</label>
              <input type="text" name="short_name" class="form-control mb-3">
            </div>
            <div class="col-lg-8">
              <label for="">Full Name *</label>
              <input type="text" name="full_name" class="form-control mb-3">
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
<div class="container-fluid card shadow">

  <table class="table mt-3 mb-3" id="exportTable">
    <thead>
      <tr>
        <th>#</th>
        <th>Short Form</th>
        <th>Full Form</th>
        <!-- <th></th> -->
      </tr>
    </thead>
    <tbody>
      @if (count($data))
      <?php $sl = 1 ?>
      @foreach ($data as $item)
      <tr>
        <td>{{$sl++}}</td>
        <td> {{$item->shortname}}</td>
        <td> {{$item->fullname}}</td>
        <!-- <td><a href="{{url('erp/admin/master/del-coglvl/'.$item->id)}}" id="citadel"><button class="btn btn-outline-danger"><i class="fas fa-trash-alt"></i></button></a></td> -->
      </tr>
      @endforeach

      @else
      <p class="display-4 text-center">No Records</p>
      @endif
    </tbody>

  </table>
</div>
@include('includes.footer')