@include('includes.header')
@include('admin.sidebar')

<h3><span class="text-uppercase">Batch Master</span></h3>
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
        <h5 class="modal-title" id="exampleModalLabel">New Batch | Session </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{url('erp/admin/master/batch')}}" method="post">
        @csrf
        <div class="modal-body">
          <label for="yearInput">Enter Year *</label>
          <input type="number" id="yearInput" name="batch_name" min="1900" max="2099" step="1" value="2026" class="form-control mb-3">
          <label for="">Admission Fees for This Year *</label>
          <input type="text" class="form-control mb-3" name="fees">

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="container-fluid card shdaow">

  <table class="table mt-3 mb-3" id="exportTable">
    <thead>
      <tr>
        <th>#</th>
        <th>Batch Name</th>
        <th>Admission Active</th>
        <th>Admission Fee <i class="fas fa-rupee-sign"></i></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @if (count($data))
      <?php $sl = 1 ?>
      @foreach ($data as $item)
      <tr>
        <td>{{$sl++}}</td>
        <td>{{$item->batch_name}}</td>
        <td>
          <a href="{{url('erp/admin/master/update-adm-batch-status/'.$item->id)}}">
            <button class="btn {{$item->admission_active_batch == 1 ? 'btn-success' : 'btn-secondary'}}">
              {{$item->admission_active_batch == 1 ? 'Active' : 'Inactive'}}
            </button>
          </a>

        </td>
        <td>{{$item->admn_fee_amount}}</td>
        <td></td>

      </tr>
      @endforeach

      @else
      <p class="display-4 text-center">No Records</p>
      @endif
    </tbody>

  </table>
</div>
@include('includes.footer')