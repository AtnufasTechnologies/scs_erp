<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BillDeskService;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Log;

class PaymentGatewayTestController extends Controller
{
  /**
   * Show payment gateway test page
   */
  public function testPage()
  {
    return view('payment-gateway-test');
  }

  /**
   * Process test payment
   */
  public function processTestPayment(Request $request)
  {
    $request->validate([
      'gateway' => 'required|in:easebuzz,billdesk',
      'amount' => 'required|numeric|min:1',
      'customer_name' => 'required|string',
      'email' => 'required|email',
      'phone' => 'required|string',
    ]);

    $gateway = $request->gateway;
    $amount = $request->amount;
    $customerName = $request->customer_name;
    $email = $request->email;
    $phone = $request->phone;

    if ($gateway === 'easebuzz') {
      return $this->processEaseBuzzTest($amount, $customerName, $email, $phone);
    } elseif ($gateway === 'billdesk') {
      return $this->processBillDeskTest($amount, $customerName, $email, $phone);
    }

    return back()->withErrors(['gateway' => 'Invalid gateway selected']);
  }

  /**
   * Process EaseBuzz test payment
   */
  private function processEaseBuzzTest($amount, $customerName, $email, $phone)
  {
    try {
      $key = env('EASEBUZZ_KEY');
      $salt = env('EASEBUZZ_SALT');
      $txnid = 'TEST' . time() . rand(1000, 9999);

      $productinfo = 'Test Payment - Salesian College';
      $split = json_encode([
        'SAL_ACFEES' => $amount
      ]);
      // Generate hash
      $hashString = "$key|$txnid|$amount|$productinfo|$customerName|$email|||||||||||$salt";
      $hash = strtolower(hash('sha512', $hashString));

      // Initiate payment
      $client = new \GuzzleHttp\Client();
      $response = $client->post(env('EASEBUZZ_INITIATE_URL'), [
        'form_params' => [
          'key' => $key,
          'txnid' => $txnid,
          'amount' => $amount,
          'productinfo' => $productinfo,
          'firstname' => $customerName,
          'phone' => $phone,
          'email' => $email,
          'surl' => route('payment.test.success'),
          'furl' => route('payment.test.failure'),
          'hash' => $hash,
          'split_payments' => $split
        ],
      ]);

      $apiResponse = json_decode($response->getBody(), true);

      Log::info('EaseBuzz Test Payment Initiated', [
        'txnid' => $txnid,
        'amount' => $amount,
        'customer_name' => $customerName,
        'email' => $email,
        'phone' => $phone,
        'api_response' => $apiResponse
      ]);

      if ($apiResponse['status'] == 1) {
        return redirect(env('EASEBUZZ_PAYMENT_URL') . $apiResponse['data']);
      } else {
        return back()->withErrors(['payment' => 'EaseBuzz payment initiation failed: ' . ($apiResponse['data'] ?? 'Unknown error')]);
      }
    } catch (\Exception $e) {
      Log::error('EaseBuzz Test Payment Error: ' . $e->getMessage());
      return back()->withErrors(['payment' => 'Payment initialization failed: ' . $e->getMessage()]);
    }
  }

  /**
   * Process BillDesk test payment
   */
  private function processBillDeskTest($amount, $customerName, $email, $phone)
  {

    $headers = ["alg" => "HS256", "clientid" => env('BILLDESK_CLIENT_ID')];
    $orderId = 'TEST' . time() . rand(1000, 9999);
    $payload = [
      "merchantid" => env('BILLDESK_MERCHANT_ID'),
      "orderid" => $orderId,
      "amount" => number_format($amount, 2, '.', ''),
      "currencycode" => "356",
      "customername" => $customerName,
      "customermobile" => $phone,
      "customeremail" => $email,
      "returnurl" => route('payment.test.billdesk.response'),
      "additionalinfo1" => 'Test Payment',
      "additionalinfo2" => $customerName,
      "additionalinfo3" => $phone,
      "additionalinfo4" => $email,
      "additionalinfo5" => 'Test Transaction',
      "additionalinfo6" => 'Salesian College',
    ];
    $randomNumber = random_int(100000000000, 999999999999);
    $traceid = "$randomNumber";
    $trace_time = date("YmdHis");
    $order_date = date("Y-m-d\TH:i:sP");
    $timestamp = date("YmdHis");

    $payload['traceid'] = $traceid;
    $payload['tracetime'] = $trace_time;
    $payload['orderdate'] = $order_date;

    Log::info('BillDesk Test Payment', [
      'order_id' => $orderId,
      'trace_id' => $traceid,
      'timestamp' => $timestamp,
      'amount' => $amount
    ]);

    $curl_payload = JWT::encode($payload, env('BILLDESK_SECRET_KEY'), "HS256", null, $headers);

    // For test, create a simple payment URL (this would come from API response in real scenario)
    $paymentUrl = env('BILLDESK_API_URL') ? str_replace('/orders/create', '/orders/' . $payload['orderid'], env('BILLDESK_API_URL')) : null;

    return view('admission.billdesk-payment', [
      'merchantId' => env('BILLDESK_MERCHANT_ID'),
      'bdOrderId' => $payload['orderid'],
      'authToken' => $curl_payload,
      'returnUrl' => route('payment.test.billdesk.response'),
      'orderId' => $orderId,
      'amount' => $amount,
      'customerName' => $customerName,
      'paymentUrl' => $paymentUrl,
      'links' => []
    ]);



    /*
    try {
      $billDeskService = new BillDeskService();

      $orderId = 'TEST' . time() . rand(1000, 9999);
      $returnUrl = route('payment.test.billdesk.response');

      $additionalInfo = [
        'info1' => 'Test Payment',
        'info2' => $customerName,
        'info3' => $phone,
        'info4' => $email,
        'info5' => 'Test Transaction',
        'info6' => 'Salesian College',
      ];

      $response = $billDeskService->createOrder($orderId, $amount, $customerName, $returnUrl, $additionalInfo);

      if ($response['success']) {
        return view('admission.billdesk-payment', [
          'merchantId' => $response['merchantId'],
          'bdOrderId' => $response['bdOrderId'],
          'authToken' => $response['authToken'],
          'returnUrl' => $returnUrl,
          'amount' => $amount,
          'customerName' => $customerName
        ]);
      } else {
        return back()->withErrors(['payment' => 'BillDesk payment initiation failed: ' . ($response['error'] ?? 'Unknown error')]);
      }
    } catch (\Exception $e) {
      Log::error('BillDesk Test Payment Error: ' . $e->getMessage());
      return back()->withErrors(['payment' => 'Payment initialization failed: ' . $e->getMessage()]);
    }
      */
  }

  /**
   * Test payment success (EaseBuzz)
   */
  public function testPaymentSuccess(Request $request)
  {
    $data = $request->all();

    return view('payment-test-result', [
      'status' => 'success',
      'gateway' => 'EaseBuzz',
      'data' => $data
    ]);
  }

  /**
   * Test payment failure (EaseBuzz)
   */
  public function testPaymentFailure(Request $request)
  {
    $data = $request->all();

    return view('payment-test-result', [
      'status' => 'failure',
      'gateway' => 'EaseBuzz',
      'data' => $data
    ]);
  }

  /**
   * Test payment response (BillDesk)
   */
  public function testBillDeskResponse(Request $request)
  {
    $data = $request->all();

    $status = $request->transaction_status ?? $request->status;
    $isSuccess = ($status == 'SUCCESS' || $status == 'success');

    return view('payment-test-result', [
      'status' => $isSuccess ? 'success' : 'failure',
      'gateway' => 'BillDesk',
      'data' => $data
    ]);
  }
}
