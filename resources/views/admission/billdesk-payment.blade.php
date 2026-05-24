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
      position: relative;
      z-index: 10;
    }

    #bd-payment-container {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 9999;
      background: rgba(0, 0, 0, 0.5);
      display: none;
    }

    /* When SDK is loaded, hide the loading container */
    body.sdk-loaded .payment-container {
      display: none;
    }

    body.sdk-loaded #bd-payment-container {
      display: block;
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

    #error-message {
      animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
    }
  </style>
</head>

<body>
  <div class="payment-container">
    <h2>Processing Your Payment</h2>
    <div class="loader"></div>
    <div class="customer-info">
      <p><strong>Customer:</strong> {{ $customerName }}</p>
      <p><strong>Order ID:</strong> {{ $orderId ?? 'N/A' }}</p>
      <p><strong>Amount:</strong> <span class="amount">₹ {{ number_format($amount, 2) }}</span></p>
    </div>
    <p id="status-text">Please wait while we redirect you to the payment gateway...</p>
    <p style="font-size: 12px; color: #888; margin-top: 20px;">
      Do not close this window or press the back button.
    </p>
    <div id="error-message" style="display: none; color: #dc3545; margin-top: 20px; padding: 10px; background: #f8d7da; border-radius: 5px;">
      <strong>Payment Error:</strong> <span id="error-text"></span>
    </div>
  </div>

  <!-- BillDesk Payment Flow Container -->
  <div id="bd-payment-container"></div>

  <script type="text/javascript">
    // Configuration from server
    const paymentConfig = {
      merchantId: "{{ $merchantId }}",
      bdOrderId: "{{ $bdOrderId }}",
      authToken: "{{ $authToken }}",
      returnUrl: "{{ $returnUrl }}",
      @if(isset($paymentUrl))
      paymentUrl: "{{ $paymentUrl }}",
      @endif
    };

    console.log('Payment Config:', paymentConfig);

    function updateStatus(message) {
      const statusText = document.getElementById('status-text');
      if (statusText) {
        statusText.textContent = message;
      }
    }

    function showError(message) {
      console.error('Payment Error:', message);
      const errorDiv = document.getElementById('error-message');
      const errorText = document.getElementById('error-text');
      if (errorText) errorText.textContent = message;
      if (errorDiv) errorDiv.style.display = 'block';

      // Hide loader
      const loader = document.querySelector('.loader');
      if (loader) loader.style.display = 'none';
    }

    function initiateBillDeskPayment() {
      try {
        console.log('Checking BillDesk SDK...');

        // Check if SDK is loaded
        if (typeof window.loadBillDeskSdk !== 'function') {
          console.warn('BillDesk SDK not available');
          showError('Payment gateway not loaded. Please refresh the page.');
          return;
        }

        updateStatus('Initializing payment gateway...');

        const flowConfig = {
          merchantId: paymentConfig.merchantId,
          bdOrderId: paymentConfig.bdOrderId,
          authToken: paymentConfig.authToken,
          childWindow: false,
          retryCount: 3,
          prefs: {
            payment_categories: ["card", "emi", "nb", "upi", "wallets", "qr"]
          }
        };

        // Response handler for payment callbacks
        const responseHandler = function(txn) {
          console.log('BillDesk Response:', txn);

          try {
            // Handle different response statuses
            if (txn && txn.status) {
              switch (txn.status) {
                case 'success':
                  updateStatus('Payment successful! Redirecting...');
                  window.location.href = paymentConfig.returnUrl + '?status=success&bdorder_id=' + paymentConfig.bdOrderId;
                  break;
                case 'failure':
                  showError('Payment failed: ' + (txn.message || 'Transaction declined'));
                  break;
                case 'pending':
                  updateStatus('Payment is being processed...');
                  break;
                case 'cancelled':
                case 'cancel':
                  showError('Payment was cancelled by user');
                  setTimeout(() => {
                    window.location.href = paymentConfig.returnUrl + '?status=cancelled';
                  }, 2000);
                  break;
                default:
                  console.warn('Unknown status:', txn.status);
              }
            }
          } catch (e) {
            console.error('Error in response handler:', e);
          }
        };

        const config = {
          flowConfig: flowConfig,
          flowType: "payments",
          responseHandler: responseHandler
        };

        console.log('Loading BillDesk SDK with config:', config);
        updateStatus('Loading payment options...');

        // Initialize the SDK
        const result = window.loadBillDeskSdk(config);
        console.log('SDK Load Result:', result);

        // Mark SDK as loaded
        document.body.classList.add('sdk-loaded');

      } catch (error) {
        console.error('Error initiating BillDesk payment:', error);
        showError('Failed to initialize payment: ' + error.message);
      }
    }

    // Wait for both DOM and SDK to load
    let sdkLoadAttempts = 0;
    const maxAttempts = 10;

    function checkAndInitialize() {
      sdkLoadAttempts++;
      console.log(`SDK check attempt ${sdkLoadAttempts}/${maxAttempts}`);

      if (typeof window.loadBillDeskSdk === 'function') {
        console.log('BillDesk SDK loaded successfully');
        updateStatus('Connecting to payment gateway...');
        setTimeout(initiateBillDeskPayment, 500);
      } else if (sdkLoadAttempts < maxAttempts) {
        console.log('SDK not ready yet, retrying...');
        setTimeout(checkAndInitialize, 1000);
      } else {
        console.error('BillDesk SDK failed to load after ' + maxAttempts + ' attempts');
        showError('Unable to load payment gateway. Please check your internet connection and refresh the page.');
      }
    }

    // Start when page loads
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, starting SDK check...');
        setTimeout(checkAndInitialize, 500);
      });
    } else {
      console.log('DOM already loaded, starting SDK check...');
      setTimeout(checkAndInitialize, 500);
    }

    // Global error handler
    window.addEventListener('error', function(e) {
      console.error('Global error:', e);
      if (e.message && (e.message.includes('billdesk') || e.message.includes('BillDesk'))) {
        showError('Payment gateway error. Please try again or contact support.');
      }
    });

    // Handle browser back button
    window.addEventListener('popstate', function(e) {
      e.preventDefault();
      if (confirm('Are you sure you want to cancel this payment?')) {
        window.location.href = paymentConfig.returnUrl + '?status=cancelled';
      }
    });
  </script>
</body>

</html>