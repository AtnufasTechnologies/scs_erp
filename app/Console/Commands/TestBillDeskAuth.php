<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Tests BillDesk JOSE authentication (JWS wrapping JWE).
 *
 * BillDesk auth flow:
 *   1. JSON payload
 *   2. JWE-encrypt  → DIR + A256GCM  (AES-256-GCM, key = BILLDESK_SECRET_KEY)
 *   3. JWS-sign     → HS256          (HMAC-SHA256, key = BILLDESK_SECRET_KEY)
 *   4. HTTP POST with Content-Type: application/jose
 *
 * Response decoding:
 *   1. JWS-verify   → extract JWE payload
 *   2. JWE-decrypt  → get original JSON
 */
class TestBillDeskAuth extends Command
{
  protected $signature   = 'billdesk:test-auth';
  protected $description = 'Test BillDesk JOSE (JWE+JWS) authentication against the UAT API';

  public function handle(): void
  {
    $merchantId = env('BILLDESK_MERCHANT_ID');
    $clientId   = env('BILLDESK_CLIENT_ID');
    $keyId      = env('BILLDESK_KEY_ID');
    $secretKey  = env('BILLDESK_SECRET_KEY');  // JWE encryption key (AES-256-GCM)
    $signKey    = env('BILLDESK_SIGN_KEY');    // JWS signing key (HMAC-SHA256)
    $apiUrl     = env('BILLDESK_API_URL');

    $this->line('');
    $this->info('=== BillDesk JOSE Auth Test ===');
    $this->line("  Merchant ID    : $merchantId");
    $this->line("  Client ID      : $clientId");
    $this->line("  Key ID         : $keyId");
    $this->line("  Enc key length : " . strlen($secretKey) . " bytes");
    $this->line("  Sign key length: " . strlen($signKey) . " bytes");
    $this->line("  API URL        : $apiUrl");
    $this->line('');

    // ── Self-test: verify local JWE+JWS round-trip ───────────────────────────
    $this->info('--- Self-test (local round-trip) ---');
    $testData = json_encode(['test' => 'hello', 'ts' => time()]);
    $jweTest  = $this->encryptJWE($testData, $secretKey, $keyId, $clientId);
    $jwsTest  = $this->signJWS($jweTest, $signKey, $keyId, $clientId);
    $jweBack  = $this->verifyJWS($jwsTest, $signKey);
    $plain    = $this->decryptJWE($jweBack, $secretKey);
    if ($plain === $testData) {
      $this->info('  JWE+JWS round-trip: PASS');
    } else {
      $this->error('  JWE+JWS round-trip: FAIL — decrypted data does not match');
      return;
    }
    $this->line('');

    // ── Step 1: Build minimal create-order JSON payload ──────────────────────
    $orderId = 'TEST' . time() . rand(100, 999);
    $payload = [
      'mercid'     => $merchantId,
      'orderid'    => $orderId,
      'amount'     => '10.00',
      'order_date' => date("Y-m-d\TH:i:sP"),
      'currency'   => '356',
      'ru'         => 'https://example.com/return',
      'itemcode'   => 'DIRECT',
      'device'     => [
        'init_channel'  => 'internet',
        'ip'            => env('BILLDESK_IP_ADDRESS', '127.0.0.1'),
        'accept_header' => 'text/html',
        'user_agent'    => 'Mozilla/5.0',
      ],
    ];

    $json = json_encode($payload);
    $this->info('--- API Test ---');
    $this->line("Step 1 — JSON payload built (order: $orderId)");

    // ── Step 2: JWE encrypt ───────────────────────────────────────────────────
    try {
      $jwe = $this->encryptJWE($json, $secretKey, $keyId, $clientId);
      $this->line("Step 2 — JWE encrypted OK (" . strlen($jwe) . " chars)");
    } catch (\Exception $e) {
      $this->error("JWE encryption failed: " . $e->getMessage());
      return;
    }

    // ── Step 3: JWS sign ─────────────────────────────────────────────────────
    try {
      $jws = $this->signJWS($jwe, $signKey, $keyId, $clientId);
      $this->line("Step 3 — JWS signed OK (" . strlen($jws) . " chars)");
    } catch (\Exception $e) {
      $this->error("JWS signing failed: " . $e->getMessage());
      return;
    }

    // ── Step 4: Send HTTP request ─────────────────────────────────────────────
    $traceid   = (string) random_int(100000000000, 999999999999);
    $timestamp = date("YmdHis");

    $this->line("Step 4 — Sending request (traceid: $traceid)");

    $headers = [
      'Content-Type: application/jose',
      'Accept: application/jose',
      "BD-Traceid: $traceid",
      "BD-Timestamp: $timestamp",
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jws);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_HEADER, false); // Don't include headers in output

    $result    = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErrNo = curl_errno($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    $this->line('');
    $this->info("HTTP Status: $httpCode");

    if ($curlErrNo) {
      $this->error("cURL error $curlErrNo: $curlError");
      return;
    }

    $this->line("Raw response (" . strlen($result) . " chars): " . substr($result, 0, 300));
    $this->line('');

    if (empty($result)) {
      $this->warn("Empty response from BillDesk.");
      return;
    }

    // Try JOSE decode first; error responses from BillDesk are plain JSON
    try {
      $jwePayload = $this->verifyJWS($result, $signKey);
      $this->line("JWS signature verified OK");
      $plaintext = $this->decryptJWE($jwePayload, $secretKey);
      $this->line("JWE decrypted OK");
      $decoded = json_decode($plaintext, true);
      $this->info("Decoded response:");
      $this->line(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    } catch (\Exception $e) {
      $this->warn("JOSE decode failed (" . $e->getMessage() . "), trying plain JSON...");
      $decoded = json_decode($result, true);
      if ($decoded) {
        $this->line(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
      } else {
        $this->line("Raw: $result");
      }
    }
  }

  // ───────────────────────────────────────────────────────────────────────────
  //  JOSE helpers
  // ───────────────────────────────────────────────────────────────────────────

  private function b64u(string $data): string
  {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
  }

  private function b64uDecode(string $data): string
  {
    $pad = strlen($data) % 4;
    if ($pad) {
      $data .= str_repeat('=', 4 - $pad);
    }
    return base64_decode(strtr($data, '-_', '+/'));
  }

  /**
   * JWE compact: header . "" . iv . ciphertext . tag  (all BASE64URL encoded)
   * alg=dir, enc=A256GCM, AAD=BASE64URL(header)
   */
  private function encryptJWE(string $plaintext, string $key, string $keyId, string $clientId): string
  {
    $headerJson = json_encode([
      'alg'      => 'dir',
      'enc'      => 'A256GCM',
      'kid'      => $keyId,
      'clientid' => $clientId,
    ]);
    $header = $this->b64u($headerJson);
    $iv     = random_bytes(12);
    $tag    = '';

    $ciphertext = openssl_encrypt(
      $plaintext,
      'aes-256-gcm',
      $key,
      OPENSSL_RAW_DATA,
      $iv,
      $tag,
      $header,
      16
    );

    if ($ciphertext === false) {
      throw new \Exception('openssl_encrypt failed: ' . openssl_error_string());
    }

    return $header . '..' . $this->b64u($iv) . '.' . $this->b64u($ciphertext) . '.' . $this->b64u($tag);
  }

  /**
   * JWS compact: header . payload . signature  (all BASE64URL encoded)
   * alg=HS256
   */
  private function signJWS(string $payload, string $key, string $keyId, string $clientId): string
  {
    $headerJson = json_encode([
      'alg'      => 'HS256',
      'kid'      => $keyId,
      'clientid' => $clientId,
    ]);
    $header         = $this->b64u($headerJson);
    $encodedPayload = $this->b64u($payload);
    $signingInput   = $header . '.' . $encodedPayload;
    $signature      = hash_hmac('sha256', $signingInput, $key, true);

    return $signingInput . '.' . $this->b64u($signature);
  }

  private function verifyJWS(string $jws, string $key): string
  {
    $parts = explode('.', $jws);
    if (count($parts) !== 3) {
      throw new \Exception('Invalid JWS: expected 3 parts, got ' . count($parts));
    }
    [$header, $payload, $signature] = $parts;

    $expected = hash_hmac('sha256', $header . '.' . $payload, $key, true);
    if (!hash_equals($expected, $this->b64uDecode($signature))) {
      throw new \Exception('JWS signature mismatch');
    }

    return $this->b64uDecode($payload);
  }

  private function decryptJWE(string $jwe, string $key): string
  {
    $parts = explode('.', $jwe);
    if (count($parts) !== 5) {
      throw new \Exception('Invalid JWE: expected 5 parts, got ' . count($parts));
    }
    [$header,, $iv, $ciphertext, $tag] = $parts;

    $plaintext = openssl_decrypt(
      $this->b64uDecode($ciphertext),
      'aes-256-gcm',
      $key,
      OPENSSL_RAW_DATA,
      $this->b64uDecode($iv),
      $this->b64uDecode($tag),
      $header
    );

    if ($plaintext === false) {
      throw new \Exception('openssl_decrypt failed: ' . openssl_error_string());
    }

    return $plaintext;
  }
}
