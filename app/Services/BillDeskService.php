<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * BillDesk Payment Gateway Service
 *
 * Uses JOSE (nested JWE + JWS) for all API communication:
 *   Request  : JSON → JWE-encrypt (AES-256-GCM, SECRET_KEY) → JWS-sign (HS256, SIGN_KEY)
 *   Response : JWS-verify (SIGN_KEY) → JWE-decrypt (SECRET_KEY) → JSON
 */
class BillDeskService
{
  private $merchantId;
  private $clientId;
  private $keyId;
  private $secretKey;   // AES-256-GCM encryption key  (BILLDESK_SECRET_KEY)
  private $signKey;     // HMAC-SHA256 signing key      (BILLDESK_SIGN_KEY)
  private $apiUrl;
  private $ipAddress;

  public function __construct()
  {
    $this->merchantId = env('BILLDESK_MERCHANT_ID');
    $this->clientId   = env('BILLDESK_CLIENT_ID');
    $this->keyId      = env('BILLDESK_KEY_ID');
    $this->secretKey  = env('BILLDESK_SECRET_KEY');
    $this->signKey    = env('BILLDESK_SIGN_KEY');
    $this->apiUrl     = env('BILLDESK_API_URL');
    $this->ipAddress  = env('BILLDESK_IP_ADDRESS', request()->ip());
  }

  // ─────────────────────────────────────────────────────────────────────────
  //  Public API methods
  // ─────────────────────────────────────────────────────────────────────────

  /**
   * Generate unique order number
   */
  public function generateUniqueNum($length = 5)
  {
    $chars  = '53194931851485852262767284719384643';
    $result = '';
    for ($p = 0; $p < $length; $p++) {
      $result .= ($p % 2) ? $chars[mt_rand(19, 23)] : $chars[mt_rand(0, 18)];
    }
    $x = explode('-', date('Y-m-d-H-i-s'));
    return $x[1] . $x[2] . $x[3] . 'TM' . $result;
  }

  /**
   * Create order with BillDesk
   *
   * @param string $orderId
   * @param float  $amount
   * @param string $customerName
   * @param string $returnUrl
   * @param array  $additionalInfo
   * @param array  $customerInfo   Keys: email, mobile
   * @return array  ['success'=>true, 'bdOrderId'=>..., 'authToken'=>..., 'merchantId'=>...]
   * @throws Exception
   */
  public function createOrder($orderId, $amount, $customerName, $returnUrl, $additionalInfo = [], $customerInfo = [])
  {
    $traceid    = (string) random_int(100000000000, 999999999999);
    $traceTime  = date('YmdHis');
    $orderDate  = date("Y-m-d\TH:i:sP");
    $userAgent  = request()->header('User-Agent') ?? 'Mozilla/5.0';

    $payload = [
      'mercid'     => $this->merchantId,
      'orderid'    => $orderId,
      'amount'     => number_format($amount, 2, '.', ''),
      'order_date' => $orderDate,
      'currency'   => '356',
      'ru'         => $returnUrl,
      'itemcode'   => 'DIRECT',
      'device'     => [
        'init_channel'               => 'internet',
        'ip'                         => $this->ipAddress,
        'accept_header'              => 'text/html',
        'user_agent'                 => $userAgent,
        'browser_tz'                 => '-330',
        'browser_color_depth'        => '32',
        'browser_java_enabled'       => 'false',
        'browser_screen_height'      => '601',
        'browser_screen_width'       => '657',
        'browser_language'           => 'en-US',
        'browser_javascript_enabled' => 'true',
      ],
    ];

    if (!empty($customerInfo)) {
      $customer = [];
      if (isset($customerInfo['email']))  $customer['email_id']  = $customerInfo['email'];
      if (isset($customerInfo['mobile'])) $customer['mobile_no'] = $customerInfo['mobile'];
      if (!empty($customer)) $payload['customer'] = $customer;
    }

    $payload['additional_info'] = [
      'additional_info1' => $additionalInfo['info1'] ?? 'Application Fee',
      'additional_info2' => $customerName,
      'additional_info3' => $additionalInfo['info3'] ?? '',
      'additional_info4' => $additionalInfo['info4'] ?? '',
      'additional_info5' => $additionalInfo['info5'] ?? '',
      'additional_info6' => $additionalInfo['info6'] ?? '',
      'additional_info7' => $additionalInfo['info7'] ?? '',
    ];

    Log::info('BillDesk createOrder - payload', ['orderid' => $orderId, 'amount' => $payload['amount']]);

    $body     = $this->joseEncode(json_encode($payload));
    $result   = $this->post($this->apiUrl, $body, $traceid, $traceTime);
    $response = $this->joseDecode($result);

    Log::info('BillDesk createOrder - response', ['status' => $response['status'] ?? null, 'orderid' => $orderId]);

    if (isset($response['status']) && $response['status'] === 'ACTIVE') {
      $authorizationToken = null;
      foreach ($response['links'] ?? [] as $link) {
        if (isset($link['headers']['authorization'])) {
          $authorizationToken = $link['headers']['authorization'];
          break;
        }
      }
      return [
        'success'    => true,
        'bdOrderId'  => $response['bdorderid'] ?? null,
        'authToken'  => $authorizationToken,
        'merchantId' => $this->merchantId,
        'returnUrl'  => $returnUrl,
        'links'      => $response['links'] ?? [],
        'order_date' => $response['order_date'] ?? null,
      ];
    }

    return [
      'success'    => false,
      'error'      => $response['message'] ?? $response['error_description'] ?? 'Order creation failed',
      'error_code' => $response['error_code'] ?? null,
      'response'   => $response,
    ];
  }

  /**
   * Retrieve/Verify payment transaction
   *
   * @param string $orderId
   * @return array
   */
  public function verifyTransaction($orderId)
  {
    $traceid   = (string) random_int(100000000000, 999999999999);
    $traceTime = date('YmdHis');
    $apiUrl    = str_replace('/orders/create', '/transactions/get', $this->apiUrl);

    $payload = [
      'mercid'  => $this->merchantId,
      'orderid' => $orderId,
    ];

    $body   = $this->joseEncode(json_encode($payload));
    $result = $this->post($apiUrl, $body, $traceid, $traceTime);

    try {
      $response = $this->joseDecode($result);
    } catch (Exception $e) {
      return ['success' => false, 'error' => 'Decode error: ' . $e->getMessage()];
    }

    return [
      'success'             => true,
      'transaction_status'  => $response['transaction_status'] ?? null,
      'amount'              => $response['amount'] ?? null,
      'transaction_date'    => $response['transaction_date'] ?? null,
      'payment_method_type' => $response['payment_method_type'] ?? null,
      'data'                => $response,
    ];
  }

  /**
   * Parse and verify the JOSE-encoded webhook/callback response from BillDesk.
   *
   * @param string $joseResponse
   * @return array
   */
  public function parseWebhookResponse($joseResponse)
  {
    try {
      $decoded = $this->joseDecode($joseResponse);
      return [
        'success'            => true,
        'orderid'            => $decoded['orderid'] ?? null,
        'transaction_status' => $decoded['transaction_status'] ?? null,
        'amount'             => $decoded['amount'] ?? null,
        'transaction_date'   => $decoded['transaction_date'] ?? null,
        'objectid'           => $decoded['objectid'] ?? null,
        'response_code'      => $decoded['transaction_error_code'] ?? null,
        'response_message'   => $decoded['transaction_error_desc'] ?? null,
        'data'               => $decoded,
      ];
    } catch (Exception $e) {
      return [
        'success' => false,
        'error'   => 'Invalid JOSE response: ' . $e->getMessage(),
      ];
    }
  }

  // ─────────────────────────────────────────────────────────────────────────
  //  JOSE helpers  (JWE = AES-256-GCM, JWS = HS256)
  // ─────────────────────────────────────────────────────────────────────────

  /**
   * Encode: JSON → JWE(AES-256-GCM) → JWS(HS256)
   */
  private function joseEncode(string $json): string
  {
    return $this->jwsSign($this->jweEncrypt($json));
  }

  /**
   * Decode: JWS → verify → JWE → decrypt → array
   */
  private function joseDecode(string $token): array
  {
    $jwe  = $this->jwsVerify($token);
    $json = $this->jweDecrypt($jwe);
    $data = json_decode($json, true);
    if ($data === null) {
      throw new Exception('joseDecode: json_decode failed on: ' . substr($json, 0, 200));
    }
    return $data;
  }

  /**
   * JWE compact serialisation: DIR + A256GCM
   * Header: {"alg":"dir","enc":"A256GCM","kid":"...","clientid":"..."}
   * AAD   : BASE64URL(header)
   */
  private function jweEncrypt(string $plaintext): string
  {
    $header = $this->b64u(json_encode([
      'alg'      => 'dir',
      'enc'      => 'A256GCM',
      'kid'      => $this->keyId,
      'clientid' => $this->clientId,
    ]));

    $iv         = random_bytes(12);
    $tag        = '';
    $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $this->secretKey, OPENSSL_RAW_DATA, $iv, $tag, $header, 16);

    if ($ciphertext === false) {
      throw new Exception('JWE encrypt failed: ' . openssl_error_string());
    }

    return $header . '..' . $this->b64u($iv) . '.' . $this->b64u($ciphertext) . '.' . $this->b64u($tag);
  }

  /**
   * JWE compact decryption
   */
  private function jweDecrypt(string $jwe): string
  {
    $parts = explode('.', $jwe);
    if (count($parts) !== 5) {
      throw new Exception('JWE: expected 5 parts, got ' . count($parts));
    }
    [$header,, $iv, $ciphertext, $tag] = $parts;

    $plaintext = openssl_decrypt(
      $this->b64uDecode($ciphertext),
      'aes-256-gcm',
      $this->secretKey,
      OPENSSL_RAW_DATA,
      $this->b64uDecode($iv),
      $this->b64uDecode($tag),
      $header
    );

    if ($plaintext === false) {
      throw new Exception('JWE decrypt failed: ' . openssl_error_string());
    }

    return $plaintext;
  }

  /**
   * JWS compact: HS256
   * Header: {"alg":"HS256","kid":"...","clientid":"..."}
   */
  private function jwsSign(string $payload): string
  {
    $header       = $this->b64u(json_encode(['alg' => 'HS256', 'kid' => $this->keyId, 'clientid' => $this->clientId]));
    $encPayload   = $this->b64u($payload);
    $signingInput = $header . '.' . $encPayload;
    $signature    = hash_hmac('sha256', $signingInput, $this->signKey, true);

    return $signingInput . '.' . $this->b64u($signature);
  }

  /**
   * JWS compact verification — returns the decoded payload (the JWE string).
   */
  private function jwsVerify(string $jws): string
  {
    $parts = explode('.', $jws);
    if (count($parts) !== 3) {
      throw new Exception('JWS: expected 3 parts, got ' . count($parts));
    }
    [$header, $payload, $signature] = $parts;

    $expected = hash_hmac('sha256', $header . '.' . $payload, $this->signKey, true);
    if (!hash_equals($expected, $this->b64uDecode($signature))) {
      throw new Exception('JWS signature verification failed');
    }

    return $this->b64uDecode($payload);
  }

  private function b64u(string $data): string
  {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
  }

  private function b64uDecode(string $data): string
  {
    $pad = strlen($data) % 4;
    if ($pad) $data .= str_repeat('=', 4 - $pad);
    return base64_decode(strtr($data, '-_', '+/'));
  }

  // ─────────────────────────────────────────────────────────────────────────
  //  HTTP helper
  // ─────────────────────────────────────────────────────────────────────────

  /**
   * POST a JOSE body to a BillDesk endpoint and return the raw response string.
   *
   * @throws Exception on cURL error or non-2xx HTTP status
   */
  private function post(string $url, string $body, string $traceid, string $timestamp): string
  {
    $headers = [
      'Content-Type: application/jose',
      'Accept: application/jose',
      "BD-Traceid: $traceid",
      "BD-Timestamp: $timestamp",
    ];

    $ch = curl_init($url);

    // More aggressive header setting for live servers
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_ENCODING, ''); // Accept any encoding
    curl_setopt($ch, CURLOPT_USERAGENT, 'BillDesk-PHP-Client/1.0');

    // Ensure we're sending as POST with proper content-type
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, null); // Clear any custom request

    // Enable verbose mode in debug
    if (config('app.debug')) {
      curl_setopt($ch, CURLOPT_VERBOSE, true);
      $verbose = fopen('php://temp', 'w+');
      curl_setopt($ch, CURLOPT_STDERR, $verbose);
    }

    $result    = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErrNo = curl_errno($ch);
    $curlError = curl_error($ch);
    $curlInfo  = curl_getinfo($ch);

    // Log verbose output if debugging
    if (config('app.debug') && isset($verbose)) {
      rewind($verbose);
      $verboseLog = stream_get_contents($verbose);
      if ($verboseLog) {
        Log::debug('cURL Verbose', ['output' => $verboseLog]);
      }
      fclose($verbose);
    }

    curl_close($ch);

    Log::info('BillDesk HTTP Request', [
      'url' => $url,
      'http_code' => $httpCode,
      'traceid' => $traceid,
      'body_length' => strlen($body),
      'request_headers' => $headers,
      'content_type' => $curlInfo['content_type'] ?? 'unknown'
    ]);

    if ($curlErrNo) {
      throw new Exception("cURL error $curlErrNo: $curlError");
    }

    if ($httpCode < 200 || $httpCode >= 300) {
      // Error responses from BillDesk are plain JSON (not JOSE)
      $err = json_decode($result, true);
      if ($err) {
        Log::error('BillDesk API Error', [
          'http_code' => $httpCode,
          'error' => $err,
          'traceid' => $traceid,
          'url' => $url
        ]);
        $msg = $err['message'] ?? $err['error_description'] ?? 'Unknown error';
        $code = $err['error_code'] ?? '';
        throw new Exception("HTTP $httpCode [$code]: $msg");
      }
      throw new Exception("HTTP $httpCode: " . substr($result, 0, 200));
    }

    return $result;
  }
}
