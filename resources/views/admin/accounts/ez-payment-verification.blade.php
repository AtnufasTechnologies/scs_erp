@include('includes.header')
@include('admin.sidebar')

<h3><span class="text-uppercase">EaseBuzz Payment Verification </span></h3>

<div class="container mt-4">
  @if(isset($data))
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow-sm" id="print-section">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0 text-uppercase">EaseBuzz Payment Verification</h5>
          <button class="btn btn-sm btn-light" onclick="printSection('print-section')">
            <i class="bi bi-printer"></i> Print
          </button>
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
      </div>
    </div>
  </div>
  <script>
    function printSection(sectionId) {
      var printContents = document.getElementById(sectionId).innerHTML;
      var originalContents = document.body.innerHTML;
      document.body.innerHTML = printContents;
      window.print();
      document.body.innerHTML = originalContents;
      location.reload();
    }
  </script>
  @else
  <div class="alert alert-info mt-3">
    No payment verification data available.
  </div>
  @endif
</div>



@include('includes.footer')