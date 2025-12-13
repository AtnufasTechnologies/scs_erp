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
      </tr>
      @endforeach
      @else
      <p class="display-4 text-center">No Records Found</p>
      @endif
    </tbody>
  </table>
  @include('includes.footer')