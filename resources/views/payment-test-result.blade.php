<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Test Result</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 50px 0;
    }

    .result-container {
      max-width: 800px;
      margin: 0 auto;
    }

    .card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }

    .result-header {
      padding: 40px;
      text-align: center;
      border-radius: 15px 15px 0 0;
    }

    .result-header.success {
      background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .result-header.failure {
      background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
    }

    .result-icon {
      font-size: 80px;
      color: white;
      margin-bottom: 20px;
    }

    .result-title {
      color: white;
      font-size: 32px;
      font-weight: bold;
      margin: 0;
    }

    .result-body {
      padding: 30px;
    }

    .data-row {
      padding: 10px;
      border-bottom: 1px solid #eee;
    }

    .data-row:last-child {
      border-bottom: none;
    }

    .data-label {
      font-weight: bold;
      color: #555;
    }

    .data-value {
      color: #333;
      word-break: break-all;
    }

    .btn-back {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      padding: 12px 40px;
      color: white;
      border-radius: 50px;
      font-weight: bold;
      text-decoration: none;
      display: inline-block;
      margin-top: 20px;
    }

    .btn-back:hover {
      transform: scale(1.05);
      color: white;
    }

    .gateway-badge {
      display: inline-block;
      padding: 5px 15px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 20px;
      color: white;
      font-size: 14px;
      margin-top: 10px;
    }
  </style>
</head>

<body>
  <div class="container result-container">
    <div class="card">
      <div class="result-header {{ $status }}">
        @if($status === 'success')
        <div class="result-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <h1 class="result-title">Payment Successful!</h1>
        <div class="gateway-badge">{{ $gateway }} Payment Gateway</div>
        @else
        <div class="result-icon">
          <i class="fas fa-times-circle"></i>
        </div>
        <h1 class="result-title">Payment Failed</h1>
        <div class="gateway-badge">{{ $gateway }} Payment Gateway</div>
        @endif
      </div>

      <div class="result-body">
        <h4 class="mb-4">
          <i class="fas fa-info-circle"></i> Transaction Details
        </h4>

        @if($status === 'success')
        <div class="alert alert-success">
          <strong><i class="fas fa-check"></i> Test Payment Completed Successfully!</strong><br>
          This is a test transaction. No actual money has been charged.
        </div>
        @else
        <div class="alert alert-danger">
          <strong><i class="fas fa-exclamation-triangle"></i> Test Payment Failed</strong><br>
          This is a test transaction. Please check the error details below.
        </div>
        @endif

        <div class="mt-4">
          @foreach($data as $key => $value)
          @if(!is_array($value) && !is_object($value))
          <div class="data-row row">
            <div class="col-md-4 data-label">
              {{ ucfirst(str_replace('_', ' ', $key)) }}
            </div>
            <div class="col-md-8 data-value">
              {{ $value ?: 'N/A' }}
            </div>
          </div>
          @endif
          @endforeach
        </div>

        <div class="mt-4 p-3" style="background: #f8f9fa; border-radius: 10px;">
          <h6><strong>Raw Response Data:</strong></h6>
          <pre style="max-height: 300px; overflow-y: auto; font-size: 12px;">{{ json_encode($data, JSON_PRETTY_PRINT) }}</pre>
        </div>

        <div class="text-center mt-4">
          <a href="{{ route('payment.gateway.test') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> Run Another Test
          </a>
        </div>
      </div>
    </div>

    <div class="text-center mt-3">
      <small class="text-white">
        <i class="fas fa-shield-alt"></i> Test Mode - No actual charges applied
      </small>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>