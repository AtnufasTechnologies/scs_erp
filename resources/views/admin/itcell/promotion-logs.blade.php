@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid p-4">
  <h4> Annual Promotion<span class="text-danger"> Logs</span> </h4>

  <table class="table table-hover" id="myTable">
    <thead>
      <tr>
        <th>#</th>
        <th>Batch</th>
        <th>Campus</th>
        <th>Roll No</th>
        <th>Student Name</th>
        <th>Promoted From</th>
        <th>Promoted To</th>
        <th>Status</th>
        <th>Created on</th>
      </tr>
    </thead>

    <tbody>
      @foreach ($data as $item)
      <tr>
        <td>{{$loop->iteration}}</td>
        <td>{{$item->batchmaster->batch_name ?? '-'}}</td>
        <td>{{$item->campusmaster->name ?? '-'}}</td>
        <td><span class="text-uppercase">{{$item->studentmaster->roll_no ?? '-'}} </span></td>
        <td>{{$item->studentmaster->first_name ?? '-'}} {{$item->studentmaster->last_name ?? '-'}}</td>
        <td>{{$item->promoted_from_year}}</td>
        <td>{{$item->promoted_to_year}}</td>
        <td><span class="badge {{$item->status == 'promoted'? 'badge-success':'badge-danger'}} ">{{$item->status}}</span></td>
        <td>{{date('d-m-Y',strtotime($item->created_at))}}</td>
      </tr>
      @endforeach

    </tbody>
  </table>


</div>

@include('includes.footer')