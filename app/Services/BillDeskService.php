<?php

namespace App\Services;

use Exception;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class BillDeskService
{
  private $merchantId;
  private $clientId;
  private $secretKey;
  private $apiUrl;
  private $ipAddress;

  public function __construct()
  {
    $this->merchantId = env('BILLDESK_MERCHANT_ID');
    $this->clientId = env('BILLDESK_CLIENT_ID');
    $this->secretKey = env('BILLDESK_SECRET_KEY');
    $this->apiUrl = env('BILLDESK_API_URL');
    $this->ipAddress = env('BILLDESK_IP_ADDRESS', request()->ip());
  }

  /**
   * Generate unique order number
   */
  public function generateUniqueNum($length = 5)
  {
    $chars = '53194931851485852262767284719384643';
    $result = '';

    for ($p = 0; $p < $length; $p++) {
      $result .= ($p % 2) ? $chars[mt_rand(19, 23)] : $chars[mt_rand(0, 18)];
    }

    $dt = date('Y-m-d-H-i-s');
    $x = explode('-', $dt);
    $result = $x[1] . $x[2] . $x[3] . "TM" . $result;

    return $result;
  }

  /**
   * Create order with BillDesk
   * 
   * @param string $orderId Unique order ID
   * @param float $amount Transaction amount
   * @param string $customerName Customer name
   * @param string $returnUrl Return URL after payment
   * @param array $additionalInfo Additional information
   * @param array $customerInfo Customer details (email, mobile, etc.)
   * @return array
   */
  public function createOrder($orderId, $amount, $customerName, $returnUrl, $additionalInfo = [], $customerInfo = [])
  {
    $headers = ["alg" => "HS256", "clientid" => $this->clientId];

    $randomNumber = random_int(100000000000, 999999999999);
    $traceid = "$randomNumber";
    $trace_time = date("YmdHis");
    $order_date = date("Y-m-d\TH:i:sP");

    $userAgent = request()->header('User-Agent') ?? 'Mozilla/5.0';

    // Build payload according to BillDesk API specification
    $payload = [
      "mercid" => $this->merchantId,
      "orderid" => $orderId,
      "amount" => number_format($amount, 2, '.', ''),
      "order_date" => $order_date,
      "currency" => "356", // INR (ISO 4217)
      "ru" => $returnUrl,
      "itemcode" => "DIRECT",
      "device" => [
        "init_channel" => "internet",
        "ip" => $this->ipAddress,
        "accept_header" => "text/html",
        "user_agent" => $userAgent,
        "browser_tz" => "-330",
        "browser_color_depth" => "32",
        "browser_java_enabled" => "false",
        "browser_screen_height" => "601",
        "browser_screen_width" => "657",
        "browser_language" => "en-US",
        "browser_javascript_enabled" => "true"
      ]
    ];

    // Add customer object (recommended by BillDesk)
    if (!empty($customerInfo)) {
      $customer = [];
      if (isset($customerInfo['email'])) {
        $customer['email_id'] = $customerInfo['email'];
      }
      if (isset($customerInfo['mobile'])) {
        $customer['mobile_no'] = $customerInfo['mobile'];
      }
      if (!empty($customer)) {
        $payload['customer'] = $customer;
      }
    }

    // Add additional_info array
    $payload['additional_info'] = [
      "additional_info1" => $additionalInfo['info1'] ?? "Application Fee",
      "additional_info2" => $customerName,
      "additional_info3" => $additionalInfo['info3'] ?? "",
      "additional_info4" => $additionalInfo['info4'] ?? "",
      "additional_info5" => $additionalInfo['info5'] ?? "",
      "additional_info6" => $additionalInfo['info6'] ?? "",
      "additional_info7" => $additionalInfo['info7'] ?? ""
    ];

    $curl_payload = JWT::encode($payload, $this->secretKey, "HS256", null, $headers);

    $ch = curl_init($this->apiUrl);

    $ch_headers = array(
      "Content-Type: application/jose",
      "accept: application/jose",
      "BD-Traceid: $traceid",
      "BD-Timestamp: $trace_time"
    );

    curl_setopt($ch, CURLOPT_HTTPHEADER, $ch_headers);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curl_payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curl_errno) {
      throw new Exception("cURL error {$curl_errno}: {$curl_error}");
    }

    // Handle HTTP errors
    if ($http_code < 200 || $http_code >= 300) {
      $errorMessage = "HTTP error {$http_code}";

      // Try to decode error response
      try {
        if (!empty($result)) {
          $error_decoded = JWT::decode($result, new Key($this->secretKey, 'HS256'));
          $errorMessage .= ": " . ($error_decoded->error_description ?? json_encode($error_decoded));
        }
      } catch (Exception $e) {
        $errorMessage .= ": Unable to decode error response";
      }

      throw new Exception($errorMessage);
    }

    // Decode BillDesk Response
    try {
      $result_decoded = JWT::decode($result, new Key($this->secretKey, 'HS256'));
      $result_array = (array) $result_decoded;

      // Check if order creation was successful
      if (isset($result_decoded->status) && $result_decoded->status == 'ACTIVE') {
        $bdOrderId = $result_array['bdorderid'] ?? null;
        $authorizationToken = null;

        // Extract authorization token from links
        if (isset($result_array['links']) && is_array($result_array['links'])) {
          foreach ($result_array['links'] as $link) {
            if (isset($link->headers->authorization)) {
              $authorizationToken = $link->headers->authorization;
              break;
            }
          }
        }

        return [
          'success' => true,
          'bdOrderId' => $bdOrderId,
          'authToken' => $authorizationToken,
          'merchantId' => $this->merchantId,
          'returnUrl' => $returnUrl,
          'links' => $result_array['links'] ?? [],
          'order_date' => $result_array['order_date'] ?? null
        ];
      } else {
        return [
          'success' => false,
          'error' => $result_decoded->error_description ?? 'Order creation failed',
          'error_code' => $result_decoded->error_code ?? null,
          'response' => $result_decoded
        ];
      }
    } catch (Exception $e) {
      throw new Exception("JWT decode error: " . $e->getMessage());
    }
  }

  /**
   * Retrieve/Verify payment transaction
   * Calls BillDesk Retrieve Transaction API
   * 
   * @param string $orderId Order ID to verify
   * @return array
   */
  public function verifyTransaction($orderId)
  {
    $headers = ["alg" => "HS256", "clientid" => $this->clientId];

    $randomNumber = random_int(100000000000, 999999999999);
    $traceid = "$randomNumber";
    $trace_time = date("YmdHis");

    $payload = [
      "mercid" => $this->merchantId,
      "orderid" => $orderId
    ];

    require_once app_path('Services/BillDesk/JWT/JWTExceptionWithPayloadInterface.php');
    require_once app_path('Services/BillDesk/JWT/BeforeValidException.php');
    require_once app_path('Services/BillDesk/JWT/ExpiredException.php');
    require_once app_path('Services/BillDesk/JWT/SignatureInvalidException.php');
    require_once app_path('Services/BillDesk/JWT/Key.php');
    require_once app_path('Services/BillDesk/JWT/JWT.php');

    $curl_payload = JWT::encode($payload, $this->secretKey, "HS256", null, $headers);

    // Use transaction retrieve API endpoint
    $apiUrl = str_replace('/orders/create', '/transactions/get', $this->apiUrl);

    $ch = curl_init($apiUrl);

    $ch_headers = array(
      "Content-Type: application/jose",
      "accept: application/jose",
      "BD-Traceid: $traceid",
      "BD-Timestamp: $trace_time"
    );

    curl_setopt($ch, CURLOPT_HTTPHEADER, $ch_headers);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curl_payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curl_errno) {
      return [
        'success' => false,
        'error' => "cURL error {$curl_errno}: {$curl_error}"
      ];
    }

    try {
      $result_decoded = JWT::decode($result, new Key($this->secretKey, 'HS256'));

      return [
        'success' => true,
        'http_code' => $http_code,
        'transaction_status' => $result_decoded->transaction_status ?? null,
        'amount' => $result_decoded->amount ?? null,
        'transaction_date' => $result_decoded->transaction_date ?? null,
        'payment_method_type' => $result_decoded->payment_method_type ?? null,
        'data' => $result_decoded
      ];
    } catch (Exception $e) {
      return [
        'success' => false,
        'error' => "JWT decode error: " . $e->getMessage()
      ];
    }
  }

  /**
   * Parse and verify webhook/callback response from BillDesk
   * 
   * @param string $jwtResponse JWT encoded response from BillDesk
   * @return array
   */
  public function parseWebhookResponse($jwtResponse)
  {
    try {
      require_once app_path('Services/BillDesk/JWT/JWTExceptionWithPayloadInterface.php');
      require_once app_path('Services/BillDesk/JWT/BeforeValidException.php');
      require_once app_path('Services/BillDesk/JWT/ExpiredException.php');
      require_once app_path('Services/BillDesk/JWT/SignatureInvalidException.php');
      require_once app_path('Services/BillDesk/JWT/Key.php');
      require_once app_path('Services/BillDesk/JWT/JWT.php');

      $decoded = JWT::decode($jwtResponse, new Key($this->secretKey, 'HS256'));

      return [
        'success' => true,
        'orderid' => $decoded->orderid ?? null,
        'transaction_status' => $decoded->transaction_status ?? null,
        'amount' => $decoded->amount ?? null,
        'transaction_date' => $decoded->transaction_date ?? null,
        'objectid' => $decoded->objectid ?? null,
        'response_code' => $decoded->transaction_error_code ?? null,
        'response_message' => $decoded->transaction_error_desc ?? null,
        'data' => $decoded
      ];
    } catch (Exception $e) {
      return [
        'success' => false,
        'error' => 'Invalid JWT response: ' . $e->getMessage()
      ];
    }
  }
}
