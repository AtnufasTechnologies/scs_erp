@include('includes.header')
@include('admin.accounts.sidebar')
<h3><span class="text-uppercase">Late Fee (Fine)</span></h3>

<div class="card shadow">
  <input type="text" name="" value="{{$data->late_fee_amount}}">
</div>
@include('includes.footer')