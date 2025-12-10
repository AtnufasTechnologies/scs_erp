@include('includes.header')
@include('admin.sidebar')

<h3><span class="text-uppercase">Subject Type Master</span></h3>

<div class="container-fluid card shdaow">

  <table class="table mt-3 mb-3" id="exportTable">
    <thead>
      <tr>
        <th>#</th>
        <th>Subject Type Name</th>

      </tr>
    </thead>
    <tbody>
      @if (count($data))
      <?php $sl = 1 ?>
      @foreach ($data as $item)
      <tr>
        <td>{{$sl++}}</td>
        <td>{{$item->title}}</td>
      </tr>
      @endforeach

      @else
      <p class="display-4 text-center">No Records</p>
      @endif
    </tbody>

  </table>
</div>
@include('includes.footer')