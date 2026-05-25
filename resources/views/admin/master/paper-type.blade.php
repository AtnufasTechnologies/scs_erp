@include('includes.header')
@include('admin.sidebar')

<h3><span class="text-uppercase">Paper Type Master</span></h3>
<!-- Button trigger modal -->
<button class="cst-button mb-3" style="--clr: #21d9c7ff;" data-bs-toggle="modal" data-bs-target="#addPaperTypeModal">
  <span class="button-decor"></span>
  <div class="button-content">
    <div class="button__icon">
      <i class="fa fa-plus-circle"></i>
    </div>
    <span class="button__text">Add New</span>
  </div>
</button>

<!-- Add Modal -->
<div class="modal fade" id="addPaperTypeModal" tabindex="-1" aria-labelledby="addPaperTypeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addPaperTypeModalLabel">Add New Paper Type</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{url('erp/admin/master/paper-type')}}" method="post">
        @csrf
        <div class="modal-body">
          <label for="paperTypeCode">Paper Type Code *</label>
          <input type="text" id="paperTypeCode" name="code" class="form-control mb-3" placeholder="e.g. T, P, TP">

          <label for="paperTypeName">Paper Type Name *</label>
          <input type="text" id="paperTypeName" name="name" class="form-control mb-3" placeholder="e.g. Theory, Practical, Theory + Practical">
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
        <th>Code</th>
        <th>Paper Type</th>
        <th>Delete</th>
      </tr>
    </thead>
    <tbody>
      @if (count($data))
      <?php $sl = 1 ?>
      @foreach ($data as $item)
      <tr>
        <td>{{$sl++}}</td>
        <td>{{$item->code}}</td>
        <td><span class="text-capitalize">{{$item->name}}</span></td>
        <td>
          <a href="{{url('erp/admin/master/del-paper-type/'.$item->id)}}" id="citadel">
            <button class="btn btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
          </a>
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