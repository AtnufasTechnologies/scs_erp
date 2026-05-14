<?php

namespace App\Services;

use Exception;
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
   */
  public function createOrder($orderId, $amount, $customerName, $returnUrl, $additionalInfo = [])
  {
    $headers = ["alg" => "HS256", "clientid" => $this->clientId];

    $randomNumber = random_int(100000000000, 999999999999);
    $traceid = "$randomNumber";
    $trace_time = date("YmdHis");
    $order_date = date("Y-m-d\TH:i:sP");

    $userAgent = request()->header('User-Agent');

    $payload = [
      "mercid" => $this->merchantId,
      "orderid" => $orderId,
      "amount" => number_format($amount, 2, '.', ''),
      "order_date" => $order_date,
      "currency" => "356", // INR
      "ru" => $returnUrl,
      "additional_info" => [
        "additional_info1" => $additionalInfo['info1'] ?? "Application Fee",
        "additional_info2" => $customerName,
        "additional_info3" => $additionalInfo['info3'] ?? "",
        "additional_info4" => $additionalInfo['info4'] ?? "",
        "additional_info5" => $additionalInfo['info5'] ?? "",
        "additional_info6" => $additionalInfo['info6'] ?? "",
      ],
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

    // Load JWT files
    require_once app_path('Services/BillDesk/JWT/JWTExceptionWithPayloadInterface.php');
    require_once app_path('Services/BillDesk/JWT/BeforeValidException.php');
    require_once app_path('Services/BillDesk/JWT/ExpiredException.php');
    require_once app_path('Services/BillDesk/JWT/SignatureInvalidException.php');
    require_once app_path('Services/BillDesk/JWT/Key.php');
    require_once app_path('Services/BillDesk/JWT/JWT.php');

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

    if ($http_code < 200 || $http_code >= 300) {
      throw new Exception("HTTP error: " . $http_code);
    }

    // Decode BillDesk Response
    try {
      $result_decoded = JWT::decode($result, new Key($this->secretKey, 'HS256'));
      $result_array = (array) $result_decoded;

      if ($result_decoded->status == 'ACTIVE') {
        $link = $result_array['links'][1];
        $bdOrderId = $result_array['bdorderid'];
        $authorizationToken = $link->headers->authorization;

        return [
          'success' => true,
          'bdOrderId' => $bdOrderId,
          'authToken' => $authorizationToken,
          'merchantId' => $this->merchantId,
          'returnUrl' => $returnUrl
        ];
      } else {
        return [
          'success' => false,
          'error' => 'Order creation failed',
          'response' => $result_decoded
        ];
      }
    } catch (Exception $e) {
      throw new Exception("JWT decode error: " . $e->getMessage());
    }
  }

  /**
   * Verify payment transaction
   */
  public function verifyTransaction($orderId)
  {
    // Implement transaction verification logic
    // This would typically involve calling BillDesk's transaction status API
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

    // Use transaction status API endpoint
    $apiUrl = str_replace('/payments/ve1_2/orders/create', '/payments/ve1_2/transactions/getById', $this->apiUrl);

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
        'data' => $result_decoded
      ];
    } catch (Exception $e) {
      return [
        'success' => false,
        'error' => "JWT decode error: " . $e->getMessage()
      ];
    }
  }
}
