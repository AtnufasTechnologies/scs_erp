@include('includes.header')
@include('admin.sidebar')
<h3>Student Master</h3>

<div class="container-fluid ">
  <table class="table table-hover" id="exportTable">
    <thead class="bg-dark text-light">
      <tr>
        <th>#</th>
        <th>Reg No</th>
        <th>Roll No</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>DOB</th>
        <th>Gender</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Religion</th>
        <th>Campus</th>
        <th>Batch</th>
        <th>Department</th>
        <th>Program </th>
        <th>Current Year</th>
      </tr>
    </thead>
    <tbody>
      @if (count($data))
      <?php $sl = 1 ?>
      @foreach ($data as $item)
      <tr>
        <td>{{$sl++}}</td>
        <td>{{$item->register_no}}</td>
        <td><a href="{{url('erp/admin/'.$item->id.'/std-profile/'.$item->roll_no)}}">
            <span class="text-uppercase btn-sm btn-success">{{$item->roll_no}}</span></a></td>
        <td class="text-capitalize">{{$item->first_name}}</td>
        <td class="text-capitalize">{{$item->last_name}}</td>
        <td>{{$item->dob}}</td>
        <td>{{$item->gender == '1' ? 'Male' :'Female'}}</td>
        <td><a href="mailto:{{$item->mail_id}}">{{$item->mail_id}}</a></td>
        <td>{{$item->mobile_no}}</td>
        <td class="text-capitalize">{{$item->religionmaster != null ? $item->religionmaster->name : ''}}</td>
        <td>{{$item->campusmaster != null ? $item->campusmaster->name : ''}}</td>
        <td>{{$item->batchmaster != null ? $item->batchmaster->batch_name : ''}}</td>
        <td>{{$item->deptmaster != null ? $item->deptmaster->name : ''}} </td>
        <td>{{$item->programgroup != null ? $item->programgroup->program_code : ''}} </td>
        <td>{{$item->current_year}}</td>

      </tr>
      @endforeach

      @else
      <p class="display-4 text-center">No Records</p>
      @endif
    </tbody>

  </table>
</div>
@include('includes.footer')