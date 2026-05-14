<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BillDesk Payment Processing</title>
  <script type="module" src="https://pay.billdesk.com/jssdk/v1/dist/billdesksdk/billdesksdk.esm.js"></script>
  <script nomodule src="https://pay.billdesk.com/jssdk/v1/dist/billdesksdk.js"></script>
  <link href="https://pay.billdesk.com/jssdk/v1/dist/billdesksdk/billdesksdk.css" rel="stylesheet">
  <style>
    body {
      font-family: Arial, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .payment-container {
      background: white;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
      text-align: center;
      max-width: 500px;
    }

    .loader {
      border: 5px solid #f3f3f3;
      border-top: 5px solid #667eea;
      border-radius: 50%;
      width: 50px;
      height: 50px;
      animation: spin 1s linear infinite;
      margin: 20px auto;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    h2 {
      color: #333;
      margin-bottom: 20px;
    }

    .amount {
      font-size: 32px;
      color: #667eea;
      font-weight: bold;
      margin: 20px 0;
    }

    .customer-info {
      margin: 20px 0;
      padding: 15px;
      background: #f8f9fa;
      border-radius: 5px;
    }

    .customer-info p {
      margin: 5px 0;
      color: #555;
    }
  </style>
</head>

<body>
  <div class="payment-container">
    <h2>Processing Your Payment</h2>
    <div class="loader"></div>
    <div class="customer-info">
      <p><strong>Customer:</strong> {{ $customerName }}</p>
      <p><strong>Amount:</strong> <span class="amount">₹ {{ number_format($amount, 2) }}</span></p>
    </div>
    <p>Please wait while we redirect you to the payment gateway...</p>
    <p style="font-size: 12px; color: #888; margin-top: 20px;">
      Do not close this window or press the back button.
    </p>
  </div>

  <script type="text/javascript">
    function initiateBillDeskPayment() {
      var flow_config = {
        merchantId: "{{ $merchantId }}",
        bdOrderId: "{{ $bdOrderId }}",
        authToken: "{{ $authToken }}",
        childWindow: false,
        returnUrl: "{{ $returnUrl }}",
        crossButtonHandling: 'Y',
        retryCount: 0,
      };

      var responseHandler = function(txn) {
        console.log('BillDesk Response:', txn);
        // Handle the response here if needed
      };

      var config = {
        flowConfig: flow_config,
        flowType: "payments"
      };

      window.loadBillDeskSdk(config);
    }

    // Auto-initiate payment when page loads
    window.addEventListener('load', function() {
      setTimeout(initiateBillDeskPayment, 1000);
    });
  </script>
</body>

</html>