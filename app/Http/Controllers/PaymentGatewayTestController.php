<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BillDeskService;
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
        ],
      ]);

      $apiResponse = json_decode($response->getBody(), true);

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
