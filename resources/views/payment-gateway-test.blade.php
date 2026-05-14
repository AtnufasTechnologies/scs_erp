<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Gateway Test - Salesian College</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 50px 0;
    }

    .test-container {
      max-width: 900px;
      margin: 0 auto;
    }

    .card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
      margin-bottom: 30px;
    }

    .card-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border-radius: 15px 15px 0 0 !important;
      padding: 20px;
    }

    .gateway-card {
      transition: all 0.3s ease;
      cursor: pointer;
      border: 3px solid transparent;
    }

    .gateway-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
    }

    .gateway-card.selected {
      border-color: #667eea;
      background: #f8f9ff;
    }

    .gateway-logo {
      height: 80px;
      object-fit: contain;
      margin: 20px 0;
    }

    .btn-test {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      padding: 15px 40px;
      font-size: 18px;
      border-radius: 50px;
      color: white;
      font-weight: bold;
      transition: all 0.3s ease;
    }

    .btn-test:hover {
      transform: scale(1.05);
      box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    }

    .test-info {
      background: #fff3cd;
      border-left: 5px solid #ffc107;
      padding: 15px;
      border-radius: 5px;
      margin-bottom: 20px;
    }

    .gateway-features {
      text-align: left;
      margin-top: 15px;
    }

    .gateway-features li {
      padding: 5px 0;
      color: #555;
    }

    .form-control {
      border-radius: 10px;
      padding: 12px;
      border: 2px solid #e0e0e0;
    }

    .form-control:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .status-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: bold;
    }

    .status-active {
      background: #28a745;
      color: white;
    }

    .status-test {
      background: #ffc107;
      color: #333;
    }
  </style>
</head>

<body>
  <div class="container test-container">
    <div class="text-center mb-4">
      <h1 class="text-white mb-3">
        <i class="fas fa-credit-card"></i> Payment Gateway Test Page
      </h1>
      <p class="text-white">Salesian College Autonomous - Integration Testing</p>
    </div>

    <div class="test-info">
      <strong><i class="fas fa-info-circle"></i> Test Mode:</strong>
      This page allows you to test different payment gateway integrations. Select a gateway below and enter test details to initiate a payment.
    </div>

    <div class="card">
      <div class="card-header">
        <h4 class="mb-0"><i class="fas fa-sliders-h"></i> Test Payment Configuration</h4>
      </div>
      <div class="card-body">
        <form id="testPaymentForm" action="{{ route('payment.test.process') }}" method="POST">
          @csrf

          <div class="row mb-4">
            <div class="col-md-6">
              <label class="form-label"><strong>Test Amount (₹)</strong></label>
              <input type="number" class="form-control" name="amount" value="1" min="1" required>
            </div>
            <div class="col-md-6">
              <label class="form-label"><strong>Customer Name</strong></label>
              <input type="text" class="form-control" name="customer_name" value="Test Customer" required>
            </div>
          </div>

          <div class="row mb-4">
            <div class="col-md-6">
              <label class="form-label"><strong>Email</strong></label>
              <input type="email" class="form-control" name="email" value="test@example.com" required>
            </div>
            <div class="col-md-6">
              <label class="form-label"><strong>Phone</strong></label>
              <input type="text" class="form-control" name="phone" value="9999999999" required>
            </div>
          </div>

          <h5 class="mb-3"><i class="fas fa-university"></i> Select Payment Gateway</h5>

          <div class="row">
            <!-- EaseBuzz Gateway -->
            <div class="col-md-6">
              <div class="card gateway-card" onclick="selectGateway('easebuzz', this)">
                <div class="card-body text-center position-relative">
                  <span class="status-badge status-active">ACTIVE</span>
                  <img src="{{ asset('admin/images/easebuzz.jpg') }}" alt="EaseBuzz" class="gateway-logo" onerror="this.src='https://via.placeholder.com/200x80?text=EaseBuzz'">
                  <h5>EaseBuzz</h5>
                  <ul class="gateway-features list-unstyled">
                    <li><i class="fas fa-check-circle text-success"></i> Quick Integration</li>
                    <li><i class="fas fa-check-circle text-success"></i> Multiple Payment Options</li>
                    <li><i class="fas fa-check-circle text-success"></i> Instant Refunds</li>
                    <li><i class="fas fa-check-circle text-success"></i> Split Payments</li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- BillDesk Gateway -->
            <div class="col-md-6">
              <div class="card gateway-card" onclick="selectGateway('billdesk', this)">
                <div class="card-body text-center position-relative">
                  <span class="status-badge status-test">TEST MODE</span>
                  <img src="{{ asset('admin/images/billdesk.jpg') }}" alt="BillDesk" class="gateway-logo" onerror="this.src='https://via.placeholder.com/200x80?text=BillDesk'">
                  <h5>BillDesk</h5>
                  <ul class="gateway-features list-unstyled">
                    <li><i class="fas fa-check-circle text-success"></i> Secure JWT Authentication</li>
                    <li><i class="fas fa-check-circle text-success"></i> EMI Options</li>
                    <li><i class="fas fa-check-circle text-success"></i> Net Banking</li>
                    <li><i class="fas fa-check-circle text-success"></i> UPI & Cards</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <input type="hidden" name="gateway" id="selectedGateway" required>

          <div class="text-center mt-4">
            <button type="submit" class="btn btn-test" disabled id="submitBtn">
              <i class="fas fa-rocket"></i> Initiate Test Payment
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-terminal"></i> Integration Status</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <h6><strong>EaseBuzz</strong></h6>
            <ul class="list-unstyled">
              <li><i class="fas fa-check text-success"></i> Service Class: Loaded</li>
              <li><i class="fas fa-check text-success"></i> Environment Variables: Configured</li>
              <li><i class="fas fa-check text-success"></i> Routes: Active</li>
              <li><i class="fas fa-check text-success"></i> Webhook: Configured</li>
            </ul>
          </div>
          <div class="col-md-6">
            <h6><strong>BillDesk</strong></h6>
            <ul class="list-unstyled">
              <li><i class="fas fa-check text-success"></i> Service Class: Loaded</li>
              <li><i class="fas fa-exclamation-triangle text-warning"></i> Environment Variables: Configure Required</li>
              <li><i class="fas fa-check text-success"></i> Routes: Active</li>
              <li><i class="fas fa-check text-success"></i> Webhook: Configured</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="text-center">
      <a href="{{ url('/') }}" class="btn btn-light">
        <i class="fas fa-home"></i> Back to Home
      </a>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function selectGateway(gateway, element) {
      // Remove selected class from all cards
      document.querySelectorAll('.gateway-card').forEach(card => {
        card.classList.remove('selected');
      });

      // Add selected class to clicked card
      element.classList.add('selected');

      // Set hidden input value
      document.getElementById('selectedGateway').value = gateway;

      // Enable submit button
      document.getElementById('submitBtn').disabled = false;
    }

    // Form validation
    document.getElementById('testPaymentForm').addEventListener('submit', function(e) {
      const gateway = document.getElementById('selectedGateway').value;
      if (!gateway) {
        e.preventDefault();
        alert('Please select a payment gateway');
      }
    });
  </script>
</body>

</html>