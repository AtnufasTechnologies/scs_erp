<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BillDesk Payment Processing</title>
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
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
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

    #error-message {
      animation: fadeIn 0.3s ease-in;
      color: #dc3545;
      margin-top: 20px;
      padding: 10px;
      background: #f8d7da;
      border-radius: 5px;
      display: none;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .btn {
      background: #667eea;
      color: white;
      border: none;
      padding: 12px 30px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
      margin-top: 20px;
      transition: background 0.3s;
    }

    .btn:hover {
      background: #5568d3;
    }
  </style>
</head>

<body>
  <div class="payment-container">
    <h2>Processing Your Payment</h2>
    <div class="loader" id="loader"></div>
    <div class="customer-info">
      <p><strong>Customer:</strong> {{ $customerName }}</p>
      <p><strong>Order ID:</strong> {{ $orderId }}</p>
      <p><strong>Amount:</strong> <span class="amount">₹ {{ number_format($amount, 2) }}</span></p>
    </div>
    <p id="status-text">Redirecting to payment gateway...</p>
    <p style="font-size: 12px; color: #888; margin-top: 20px;">
      Do not close this window or press the back button.
    </p>
    <div id="error-message">
      <strong>Error:</strong> <span id="error-text"></span>
      <br>
      <button class="btn" onclick="retryPayment()">Retry Payment</button>
      <button class="btn" onclick="goBack()" style="background: #6c757d;">Go Back</button>
    </div>
  </div>

  <script type="text/javascript">
    const paymentConfig = {
      paymentUrl: @json($paymentUrl ?? null),
      merchantId: "{{ $merchantId }}",
      bdOrderId: "{{ $bdOrderId }}",
      authToken: "{{ $authToken }}",
      returnUrl: "{{ $returnUrl }}",
      links: @json($links ?? [])
    };

    console.log('Payment Configuration:', paymentConfig);

    function showError(message) {
      document.getElementById('error-text').textContent = message;
      document.getElementById('error-message').style.display = 'block';
      document.getElementById('loader').style.display = 'none';
      document.getElementById('status-text').style.display = 'none';
    }

    function retryPayment() {
      window.location.reload();
    }

    function goBack() {
      window.history.back();
    }

    function redirectToPayment() {
      try {
        console.log('Attempting to redirect to BillDesk payment page...');
        
        // Method 1: Direct URL redirect (if payment URL is provided)
        if (paymentConfig.paymentUrl) {
          console.log('Using direct payment URL:', paymentConfig.paymentUrl);
          document.getElementById('status-text').textContent = 'Redirecting to BillDesk...';
          setTimeout(() => {
            window.location.href = paymentConfig.paymentUrl;
          }, 1500);
          return;
        }

        // Method 2: Check links array for GET method
        if (paymentConfig.links && paymentConfig.links.length > 0) {
          for (let link of paymentConfig.links) {
            if (link.method === 'GET' && link.href) {
              console.log('Found GET link:', link.href);
              document.getElementById('status-text').textContent = 'Redirecting to BillDesk...';
              setTimeout(() => {
                window.location.href = link.href;
              }, 1500);
              return;
            }
          }
        }

        // If no direct URL available, show error
        console.error('No payment URL available');
        console.error('Payment config:', paymentConfig);
        showError('Payment URL not generated. Please contact support or try again.');
        
      } catch (error) {
        console.error('Error during redirect:', error);
        showError('Failed to redirect to payment gateway: ' + error.message);
      }
    }

    // Start redirect when page loads
    window.addEventListener('load', function() {
      console.log('Page loaded, initiating payment redirect...');
      setTimeout(redirectToPayment, 1000);
    });

    // Prevent accidental page exit
    window.addEventListener('beforeunload', function(e) {
      if (!document.hidden && document.getElementById('loader').style.display !== 'none') {
        e.preventDefault();
        e.returnValue = '';
      }
    });
  </script>
</body>

</html>
