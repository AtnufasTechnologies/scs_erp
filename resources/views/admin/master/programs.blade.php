@include('includes.header')
@include('admin.sidebar')

<h3><span class="text-uppercase">Program Master</span></h3>

<div class="container-fluid card shadow">

  <table class="table mt-3 mb-3" id="exportTable">
    <thead>
      <tr>
        <th>#</th>
        <th>Campus</th>
        <th>Program Type</th>
      </tr>
    </thead>
    <tbody>
      @if (count($data))
      <?php $sl = 1 ?>
      @foreach ($data as $item)
      <tr>
        <td>{{$sl++}}</td>
        <td><span class="text-uppercase"> {{$item->campus->name}}</span></td>
        <td><span class="text-uppercase"> {{$item->name}}</span></td>

      </tr>
      @endforeach

      @else
      <p class="display-4 text-center">No Records</p>
      @endif
    </tbody>

  </table>
</div>
@include('includes.footer')