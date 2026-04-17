@include('includes.header')
@include('admin.admission.sidebar')

<h3><span class="text-uppercase">EaseBuzz Payment Verification </span></h3>

<div class="container mt-4">
  @if(isset($data))
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow-sm" id="print-section">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0 text-uppercase">EaseBuzz Payment Verification</h5>

        </div>
        <div class="card-body">
          @foreach($data as $key => $value)
          @php
          $isEmpty = is_array($value) ? empty($value) : (trim((string)$value) === '');
          @endphp
          @if(!$isEmpty)
          <div class="mb-3">
            <span class="fw-bold text-secondary">{{ $key }}:</span>
            @if(strtolower($key) === 'status' && strtolower($value) === 'success')
            <span class="ms-2 text-success fw-bold">{{ is_array($value) ? json_encode($value) : $value }}</span>
            @else
            <span class="ms-2">{{ is_array($value) ? json_encode($value) : $value }}</span>
            @endif
          </div>
          @endif
          @endforeach
        </div>
        <form action="{{route('admission.update.payment')}}" onsubmit="showLoader()" method="POST">
          @csrf
          <input type="hidden" name="id" value="{{$data['udf2'] ?? ''}}">
          <input type="hidden" name="payment_gateway_ref" value="{{$data['easepayid'] ?? ''}}">
          <input type="hidden" name="captured_amount" value="{{$data['amount'] ?? ''}}">
          <input type="hidden" name="hash" value="{{$data['hash'] ?? ''}}">
          <input type="hidden" name="payment_gateway_status" value="{{$data['status'] ?? ''}}">
          <input type="hidden" name="msg" value="{{$data['error_Message'] ?? ''}}">
          <button type="submit" class="btn btn-success mt-3" id="submitBtn">Update Payment Status</button>
        </form>

        <script>
          function showLoader() {
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...';
          }
        </script>
      </div>

    </div>

  </div>



  @else
  <div class="alert alert-info mt-3">
    No payment verification data available.
  </div>
  @endif
</div>



@include('includes.footer')