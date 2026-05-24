<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BillDeskService;

class TestBillDeskPayment extends Command
{
  protected $signature = 'billdesk:test-payment';
  protected $description = 'Test BillDesk payment order creation and get payment URL';

  public function handle()
  {
    $this->info('=== BillDesk Payment Test ===');

    try {
      $billDeskService = new BillDeskService();

      $orderId = 'TEST' . time() . rand(1000, 9999);
      $amount = 10.00;
      $customerName = 'Test Customer';
      $returnUrl = 'https://example.com/return';

      $additionalInfo = [
        'info1' => 'Test Payment',
        'info2' => $customerName,
        'info3' => '9999999999',
        'info4' => 'test@example.com',
        'info5' => 'Test Transaction',
        'info6' => 'Salesian College',
      ];

      $customerInfo = [
        'email' => 'test@example.com',
        'mobile' => '9999999999',
      ];

      $this->line("Creating order: $orderId");
      $this->line("Amount: ₹" . number_format($amount, 2));
      $this->line('');

      $response = $billDeskService->createOrder(
        $orderId,
        $amount,
        $customerName,
        $returnUrl,
        $additionalInfo,
        $customerInfo
      );

      if ($response['success']) {
        $this->info('✓ Order created successfully!');
        $this->line('');
        $this->line('Order Details:');
        $this->line('  - Order ID: ' . $orderId);
        $this->line('  - BD Order ID: ' . ($response['bdOrderId'] ?? 'N/A'));
        $this->line('  - Merchant ID: ' . ($response['merchantId'] ?? 'N/A'));
        $this->line('');

        // Extract payment URL
        $paymentUrl = null;
        foreach ($response['links'] ?? [] as $link) {
          if (isset($link['method']) && $link['method'] === 'GET' && isset($link['href'])) {
            $paymentUrl = $link['href'];
            break;
          }
        }

        if ($paymentUrl) {
          $this->info('✓ Payment URL found:');
          $this->line($paymentUrl);
          $this->line('');
          $this->info('User would be redirected to above URL');
        } else {
          $this->error('✗ No payment URL found in response');
          $this->line('');
          $this->line('Links array:');
          $this->line(json_encode($response['links'] ?? [], JSON_PRETTY_PRINT));
        }
      } else {
        $this->error('✗ Order creation failed');
        $this->line('');
        $this->line('Error: ' . ($response['error'] ?? 'Unknown'));
        $this->line('Error Code: ' . ($response['error_code'] ?? 'N/A'));

        if (isset($response['response'])) {
          $this->line('');
          $this->line('Full Response:');
          $this->line(json_encode($response['response'], JSON_PRETTY_PRINT));
        }
      }
    } catch (\Exception $e) {
      $this->error('✗ Exception occurred:');
      $this->error($e->getMessage());
      $this->line('');
      $this->line('Trace:');
      $this->line($e->getTraceAsString());
    }

    $this->line('');
    $this->info('=== Test Complete ===');
  }
}
