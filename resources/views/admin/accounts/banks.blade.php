@include('includes.header')
@include('admin.sidebar')

<h3><span class="text-uppercase">Bank Accounts </span></h3>
<!-- Button trigger modal -->
<button class="cst-button mb-3" style="--clr: #21d9c7ff;" data-bs-toggle="modal" data-bs-target="#add">
  <span class="button-decor"></span>
  <div class="button-content">
    <div class="button__icon">
      <i class="fa fa-plus-circle"></i>
    </div>
    <span class="button__text">Add New</span>
  </div>
</button>
<!-- Modal -->
<div class="modal fade" id="add" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">New </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{url('erp/admin/accounts/bankinfo')}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">

          <label for="">Account Label *</label>
          <input type="text" name="acclabel" class="form-control mb-3" placeholder="Provided by Easebuzz">

          <label for="">Account Name *</label>
          <input type="text" name="accname" class="form-control mb-3" placeholder="ex..Salesian College">

          <label for="">Account No *</label>
          <input type="text" name="accno" class="form-control mb-3" placeholder="Type Here...">

          <label for="">Bank Name *</label>
          <input type="text" name="bank" class="form-control mb-3" placeholder="Type Here...">

          <label for="">IFSC Code *</label>
          <input type="text" name="ifsc" class="form-control mb-3" placeholder="Type Here...">

          <label for="">Branch *</label>
          <input type="text" name="branch_name" class="form-control mb-3" placeholder="Type Here...">

          <label for="">Cancelled Cheque (optional)</label>
          <input type="file" name="doc" class="form-control mb-3">

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
        <th>Labels (Easebuzz)</th>
        <th>Account No</th>
        <th>Account Name</th>
        <th>Bank</th>
        <th>IFSC</th>
        <th>Branch</th>
        <th>Cancelled Cheque</th>
        <th>Edit</th>
        <th>Delete</th>

      </tr>
    </thead>
    <tbody>
      @if (count($data))
      <?php $sl = 1 ?>
      @foreach ($data as $item)
      <tr>
        <td>{{$sl++}}</td>
        <td>{{$item->acc_label}}</td>
        <td> {{$item->acc_no}}</td>
        <td>{{$item->acc_name}}</td>
        <td>{{$item->bank_name}}</td>
        <td>{{$item->ifsc}}</td>
        <td>{{$item->branch}}</td>
        <td>
          @if($item->doc != null)
          <a href="{{Storage::disk('s3')->url($item->doc)}}" target="_blank"><button class="btn btn-outline-primary">View</button></a>
          @else
          <p class="text-danger">Not Added</p>
          @endif

        </td>

        <td>

          <!-- Button trigger modal -->
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#edit{{$item->id}}">
            <i class="fa fa-edit"></i>
          </button>

          <!-- Modal -->
          <div class="modal fade" id="edit{{$item->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Edit </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{url('erp/admin/accounts/update-bankinfo')}}" method="post" enctype="multipart/form-data">
                  @csrf
                  <div class="modal-body">
                    <label for="">(Easebuzz)Account Label *</label>
                    <input type="text" name="acclabel" class="form-control mb-3" value="{{$item->acc_label}}">
                    <label for="">Account Name *</label>
                    <input type="text" name="accname" class="form-control mb-3" value="{{$item->acc_name}}">

                    <label for="">Account No *</label>
                    <input type="text" name="accno" class="form-control mb-3" value="{{$item->acc_no}}">

                    <label for="">Bank Name *</label>
                    <input type="text" name="bank" class="form-control mb-3" value="{{$item->bank_name}}">

                    <label for="">IFSC Code *</label>
                    <input type="text" name="ifsc" class="form-control mb-3" value="{{$item->ifsc}}">

                    <label for="">Branch *</label>
                    <input type="text" name="branch_name" class="form-control mb-3" value="{{$item->branch}}">

                    <label for="">Cancelled Cheque (optional)</label>
                    <input type="file" name="doc" class="form-control mb-3">
                    <input type="hidden" name="id" value="{{$item->id}}">
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Update</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </td>

        <td><a href="{{url('erp/admin/account/del-bankinfo'.$item->id)}}" id="citadel"><button class="btn btn-outline-danger"><i class="fa fa-trash-alt"></i></button></a></td>
      </tr>
      @endforeach

      @else
      <p class="display-4 text-center">No Records</p>
      @endif
    </tbody>

  </table>
</div>


@include('includes.footer')