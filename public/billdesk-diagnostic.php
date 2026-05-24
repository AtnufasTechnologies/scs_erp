<?php

// BillDesk Server Diagnostic Tool
// Place this file in your web root and access via browser
// URL: https://your-domain.com/billdesk-diagnostic.php

?>
<!DOCTYPE html>
<html>

<head>
  <title>BillDesk Server Diagnostic</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
      background: #f5f5f5;
    }

    .container {
      max-width: 800px;
      margin: 0 auto;
      background: white;
      padding: 20px;
      border-radius: 5px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    h1 {
      color: #333;
      border-bottom: 2px solid #667eea;
      padding-bottom: 10px;
    }

    .section {
      margin: 20px 0;
      padding: 15px;
      background: #f9f9f9;
      border-left: 4px solid #667eea;
    }

    .success {
      color: #28a745;
    }

    .error {
      color: #dc3545;
    }

    .warning {
      color: #ffc107;
    }

    pre {
      background: #272822;
      color: #f8f8f2;
      padding: 15px;
      border-radius: 5px;
      overflow-x: auto;
    }

    .btn {
      background: #667eea;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
    }

    .btn:hover {
      background: #5568d3;
    }
  </style>
</head>

<body>
  <div class="container">
    <h1>🔍 BillDesk Server Diagnostic Tool</h1>

    <?php
    // Check 1: PHP Version
    echo '<div class="section">';
    echo '<h3>1. PHP Configuration</h3>';
    echo '<p><strong>PHP Version:</strong> ' . PHP_VERSION . '</p>';
    echo '<p><strong>cURL Enabled:</strong> ' . (function_exists('curl_init') ? '<span class="success">✓ Yes</span>' : '<span class="error">✗ No</span>') . '</p>';
    echo '<p><strong>OpenSSL Enabled:</strong> ' . (function_exists('openssl_encrypt') ? '<span class="success">✓ Yes</span>' : '<span class="error">✗ No</span>') . '</p>';
    echo '</div>';

    // Check 2: Server IP
    echo '<div class="section">';
    echo '<h3>2. Server IP Address</h3>';

    // Try multiple methods to get IP
    $methods = [
      'SERVER_ADDR' => $_SERVER['SERVER_ADDR'] ?? 'Not available',
      'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'Not available',
      'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? 'Not available',
    ];

    foreach ($methods as $method => $ip) {
      echo "<p><strong>$method:</strong> $ip</p>";
    }

    // Get public IP
    $publicIp = @file_get_contents('https://api.ipify.org');
    echo '<p><strong>Public IP (External):</strong> ' . ($publicIp ?: 'Unable to fetch') . '</p>';
    echo '<p class="warning">⚠️ This IP must be whitelisted with BillDesk</p>';
    echo '</div>';

    // Check 3: Test cURL request with headers
    if (isset($_POST['test_curl'])) {
      echo '<div class="section">';
      echo '<h3>3. BillDesk API Test</h3>';

      $apiUrl = 'https://uat1.billdesk.com/u2/payments/ve1_2/orders/create';

      echo '<p><strong>Testing URL:</strong> ' . $apiUrl . '</p>';

      $headers = [
        'Content-Type: application/jose',
        'Accept: application/jose',
        'BD-Traceid: ' . rand(100000000000, 999999999999),
        'BD-Timestamp: ' . date('YmdHis'),
      ];

      echo '<p><strong>Headers being sent:</strong></p><pre>';
      foreach ($headers as $header) {
        echo htmlspecialchars($header) . "\n";
      }
      echo '</pre>';

      $ch = curl_init($apiUrl);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, 'test-body');
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HEADER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 10);
      curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

      // Capture verbose output
      curl_setopt($ch, CURLOPT_VERBOSE, true);
      $verbose = fopen('php://temp', 'w+');
      curl_setopt($ch, CURLOPT_STDERR, $verbose);

      $result = curl_exec($ch);
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $curlError = curl_error($ch);
      $curlInfo = curl_getinfo($ch);

      rewind($verbose);
      $verboseLog = stream_get_contents($verbose);
      fclose($verbose);
      curl_close($ch);

      echo '<p><strong>HTTP Status:</strong> ' . $httpCode . '</p>';

      if ($curlError) {
        echo '<p class="error"><strong>cURL Error:</strong> ' . htmlspecialchars($curlError) . '</p>';
      }

      // Parse response
      $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
      $responseHeaders = substr($result, 0, $headerSize);
      $responseBody = substr($result, $headerSize);

      echo '<p><strong>Response Headers:</strong></p>';
      echo '<pre>' . htmlspecialchars($responseHeaders) . '</pre>';

      echo '<p><strong>Response Body:</strong></p>';
      echo '<pre>' . htmlspecialchars(substr($responseBody, 0, 500)) . '</pre>';

      // Decode response if JSON
      $json = json_decode($responseBody, true);
      if ($json) {
        echo '<p><strong>Decoded Response:</strong></p>';
        echo '<pre>' . htmlspecialchars(json_encode($json, JSON_PRETTY_PRINT)) . '</pre>';

        // Check for specific errors
        if (isset($json['error_code'])) {
          switch ($json['error_code']) {
            case 'GNIRE0002':
              echo '<p class="error">❌ <strong>Error:</strong> Content-Type header missing or not received by BillDesk</p>';
              echo '<p class="warning">Possible causes:</p>';
              echo '<ul>';
              echo '<li>Reverse proxy stripping headers</li>';
              echo '<li>Apache/Nginx configuration issue</li>';
              echo '<li>PHP configuration restricting headers</li>';
              echo '</ul>';
              break;
            case 'GNAUE0006':
              echo '<p class="error">❌ <strong>Error:</strong> IP not whitelisted</p>';
              echo '<p>Contact BillDesk to whitelist your server IP: <strong>' . ($publicIp ?: 'Check above') . '</strong></p>';
              break;
            case 'GNAUE0003':
              echo '<p class="error">❌ <strong>Error:</strong> Authentication failed</p>';
              echo '<p>Check your BillDesk credentials (JOSE encoding)</p>';
              break;
          }
        }
      }

      echo '<p><strong>Verbose cURL Log:</strong></p>';
      echo '<pre>' . htmlspecialchars($verboseLog) . '</pre>';

      echo '</div>';
    }

    // Check 4: PHP Info (optional)
    if (isset($_POST['show_phpinfo'])) {
      echo '<div class="section">';
      echo '<h3>4. PHP Configuration Details</h3>';
      ob_start();
      phpinfo();
      $phpinfo = ob_get_clean();
      echo $phpinfo;
      echo '</div>';
    }
    ?>

    <div class="section">
      <h3>Test Actions</h3>
      <form method="POST">
        <button type="submit" name="test_curl" class="btn">Test BillDesk API Connection</button>
      </form>
      <br>
      <form method="POST">
        <button type="submit" name="show_phpinfo" class="btn">Show PHP Info</button>
      </form>
    </div>

    <div class="section">
      <h3>Next Steps</h3>
      <ol>
        <li>Verify your server's public IP address above</li>
        <li>Contact BillDesk support to whitelist this IP</li>
        <li>Run the "Test BillDesk API Connection" to verify headers</li>
        <li>If GNIRE0002 error persists, check server configuration (Apache/Nginx)</li>
      </ol>
    </div>
  </div>
</body>

</html>