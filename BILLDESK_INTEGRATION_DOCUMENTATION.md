# BillDesk Payment Gateway Integration - Documentation

## Overview

This document provides complete information about the BillDesk payment gateway integration in the Salesian College ERP system.

## Integration Date

May 13, 2026

## Files Created/Modified

### 1. Service Layer

- **File**: `app/Services/BillDeskService.php`
- **Description**: Core BillDesk service class handling order creation, JWT encoding/decoding, and transaction verification.
- **Key Methods**:
    - `createOrder()` - Creates a payment order with BillDesk
    - `verifyTransaction()` - Verifies payment transaction status
    - `generateUniqueNum()` - Generates unique order numbers

### 2. JWT Library

- **Location**: `app/Services/BillDesk/JWT/`
- **Files**:
    - JWT.php
    - Key.php
    - BeforeValidException.php
    - ExpiredException.php
    - SignatureInvalidException.php
    - JWTExceptionWithPayloadInterface.php
- **Description**: Firebase JWT library for secure token generation and validation

### 3. Controllers

#### AdmissionController (Modified)

- **File**: `app/Http/Controllers/AdmissionController.php`
- **New Methods**:
    - `processPayment()` - Routes payment to appropriate gateway
    - `initiateBillDeskPayment()` - Initiates BillDesk payment flow
    - `billDeskResponse()` - Handles BillDesk payment response
    - `webhookBillDesk()` - Webhook handler for server-to-server notifications

#### PaymentGatewayTestController (New)

- **File**: `app/Http/Controllers/PaymentGatewayTestController.php`
- **Description**: Test controller for testing both EaseBuzz and BillDesk integrations
- **Methods**:
    - `testPage()` - Displays test interface
    - `processTestPayment()` - Processes test payments
    - `processEaseBuzzTest()` - EaseBuzz test handler
    - `processBillDeskTest()` - BillDesk test handler
    - `testPaymentSuccess()` - Success callback handler
    - `testPaymentFailure()` - Failure callback handler
    - `testBillDeskResponse()` - BillDesk response handler

### 4. Views

#### Admission Views

- **File**: `resources/views/admission/billdesk-payment.blade.php`
- **Description**: BillDesk payment processing page with SDK integration
- **Features**: Auto-initiates BillDesk SDK, displays loading animation

- **File**: `resources/views/admission/payment-checkout.blade.php` (Modified)
- **Changes**: Enabled BillDesk gateway option alongside EaseBuzz

#### Test Views

- **File**: `resources/views/payment-gateway-test.blade.php`
- **Description**: Payment gateway test page
- **Features**:
    - Select between EaseBuzz and BillDesk
    - Configure test payment parameters
    - View integration status
    - Modern UI with responsive design

- **File**: `resources/views/payment-test-result.blade.php`
- **Description**: Displays test payment results
- **Features**: Shows transaction details, raw response data, success/failure status

### 5. Routes

#### routes/web.php (Modified)

**Admission Payment Routes:**

```php
// Payment gateway router
Route::post('payment-process', [AdmissionController::class, 'processPayment'])->name('admission.payment.process');

// EaseBuzz Routes
Route::post('payment-success', [AdmissionController::class, 'paymentSuccess'])->name('admission.payment.success');
Route::post('payment-failure', [AdmissionController::class, 'paymentFailure'])->name('admission.payment.failure');

// BillDesk Routes
Route::any('payment-billdesk-response', [AdmissionController::class, 'billDeskResponse'])->name('admission.payment.billdesk.response');
Route::post('payment-webhook-billdesk', [AdmissionController::class, 'webhookBillDesk'])->name('admission.payment.webhook.billdesk');
```

**Test Routes:**

```php
Route::get('payment-gateway-test', [PaymentGatewayTestController::class, 'testPage'])->name('payment.gateway.test');
Route::post('payment-test-process', [PaymentGatewayTestController::class, 'processTestPayment'])->name('payment.test.process');
Route::post('payment-test-success', [PaymentGatewayTestController::class, 'testPaymentSuccess'])->name('payment.test.success');
Route::post('payment-test-failure', [PaymentGatewayTestController::class, 'testPaymentFailure'])->name('payment.test.failure');
Route::any('payment-test-billdesk-response', [PaymentGatewayTestController::class, 'testBillDeskResponse'])->name('payment.test.billdesk.response');
```

### 6. Environment Configuration

- **File**: `.env.example` (Updated)
- **New Variables**:

```bash
# BillDesk Payment Gateway Configuration
BILLDESK_MERCHANT_ID=your_merchant_id
BILLDESK_CLIENT_ID=your_client_id
BILLDESK_SECRET_KEY=your_secret_key
BILLDESK_API_URL=https://uat.billdesk.com/payments/ve1_2/orders/create
BILLDESK_IP_ADDRESS=your_server_ip_address
```

## Setup Instructions

### 1. Environment Setup

1. Copy the BillDesk configuration from `.env.example` to your `.env` file
2. Replace placeholder values with actual BillDesk credentials:
    - `BILLDESK_MERCHANT_ID` - Your merchant ID from BillDesk
    - `BILLDESK_CLIENT_ID` - Your client ID from BillDesk
    - `BILLDESK_SECRET_KEY` - Your secret key from BillDesk
    - `BILLDESK_API_URL` - Use UAT URL for testing, production URL for live
    - `BILLDESK_IP_ADDRESS` - Your server's public IP address

### 2. Gateway Logo Setup

Place gateway logo images in `public/admin/images/`:

- `easebuzz.jpg` - EaseBuzz logo
- `billdesk.jpg` - BillDesk logo

### 3. Testing

#### Access Test Page

Navigate to: `http://your-domain/payment-gateway-test`

#### Test Flow

1. Select a payment gateway (EaseBuzz or BillDesk)
2. Enter test payment details
3. Click "Initiate Test Payment"
4. Complete payment on gateway page
5. View results on the result page

### 4. Production Deployment

#### Switch to Production URLs

Update `.env` with production URLs:

```bash
# BillDesk Production
BILLDESK_API_URL=https://api.billdesk.com/payments/ve1_2/orders/create

# EaseBuzz Production
EASEBUZZ_INITIATE_URL=https://pay.easebuzz.in/payment/initiateLink
EASEBUZZ_PAYMENT_URL=https://pay.easebuzz.in/pay/
```

#### Configure Webhooks

Set up webhook URLs in gateway dashboards:

- **BillDesk Webhook**: `https://your-domain/admission/payment-webhook-billdesk`
- **EaseBuzz Webhook**: `https://your-domain/erp/admission/admission-payment-webhook-easebuzz`

## BillDesk Integration Flow

### 1. Payment Initiation

```
User → Payment Checkout Page → Select BillDesk → Submit
↓
AdmissionController::processPayment()
↓
AdmissionController::initiateBillDeskPayment()
↓
BillDeskService::createOrder()
↓
JWT Encoding → API Call to BillDesk
↓
BillDesk Response (bdOrderId, authToken)
↓
Display billdesk-payment.blade.php
↓
Auto-load BillDesk SDK
```

### 2. Payment Processing

```
BillDesk SDK → User completes payment
↓
BillDesk redirects to returnUrl
↓
AdmissionController::billDeskResponse()
↓
Update database records
↓
Send confirmation email/SMS
↓
Redirect to success page
```

### 3. Webhook Handling

```
BillDesk Server → POST webhook
↓
AdmissionController::webhookBillDesk()
↓
Verify and update transaction
↓
Return 200 OK
```

## API Endpoints

### BillDesk API

- **Order Creation**: `POST /payments/ve1_2/orders/create`
- **Transaction Status**: `POST /payments/ve1_2/transactions/getById`

### Webhook URLs

- **BillDesk**: `/admission/payment-webhook-billdesk`
- **EaseBuzz**: `/erp/admission/admission-payment-webhook-easebuzz`

### Return URLs

- **BillDesk Success/Failure**: `/admission/payment-billdesk-response`
- **EaseBuzz Success**: `/admission/payment-success`
- **EaseBuzz Failure**: `/admission/payment-failure`

## Security Features

1. **JWT Authentication**: All BillDesk API calls use JWT tokens with HS256 encryption
2. **Secret Key**: Transaction data secured with merchant secret key
3. **Hash Verification**: EaseBuzz uses SHA512 hash for request validation
4. **IP Whitelisting**: BillDesk validates server IP address
5. **HTTPS**: All communication over secure HTTPS protocol

## Database Schema

### Payment Fields in `admission_applications` table:

- `gateway_type` - 'easebuzz' or 'billdesk'
- `payment_gateway_ref` - Transaction reference from gateway
- `captured_amount` - Amount captured
- `payment_gateway_status` - Payment status
- `hash` - Security hash

### Payment Logs in `admission_application_payment_logs` table:

- `application_id` - Reference to application
- `txnid` - Transaction ID
- `easepayid` - Gateway payment ID
- `user_id` - User reference
- `amount` - Payment amount
- `status` - Transaction status
- `msg` - Status message

## Error Handling

### Common Errors

1. **cURL Error**: Network connectivity issues
2. **JWT Decode Error**: Invalid secret key or malformed response
3. **HTTP Error**: API endpoint issues
4. **Order Creation Failed**: Invalid parameters or merchant configuration

### Logging

All errors are logged using Laravel's logging system:

```php
Log::error('BillDesk Payment Error: ' . $e->getMessage());
```

## Testing Checklist

- [ ] Environment variables configured
- [ ] Test page accessible
- [ ] EaseBuzz test payment successful
- [ ] BillDesk test payment successful
- [ ] Webhook receiving notifications
- [ ] Email notifications working
- [ ] SMS notifications working
- [ ] Database records updating correctly
- [ ] Gateway logos displaying
- [ ] Error handling working
- [ ] Response page displaying correctly

## Support

### BillDesk Support

- Documentation: Check BillDesk API documentation
- Support: Contact BillDesk technical support

### EaseBuzz Support

- Documentation: Check EaseBuzz API documentation
- Support: Contact EaseBuzz technical support

## Troubleshooting

### BillDesk Payment Not Initiating

1. Check environment variables
2. Verify merchant credentials
3. Check server IP whitelisting
4. Review error logs

### Payment Response Not Received

1. Check return URL configuration
2. Verify webhook URL is accessible
3. Check firewall settings
4. Review BillDesk dashboard

### JWT Errors

1. Verify secret key is correct
2. Check JWT library files are present
3. Ensure proper file permissions

## Future Enhancements

1. **Split Payments**: Implement campus-based split payments for BillDesk
2. **Refund Integration**: Add refund functionality
3. **Payment Analytics**: Dashboard for payment statistics
4. **Multi-currency**: Support for multiple currencies
5. **Saved Cards**: Tokenization for repeat payments
6. **EMI Options**: Configure EMI payment options

## Changelog

### Version 1.0.0 (May 13, 2026)

- Initial BillDesk integration
- Payment gateway test page created
- Both EaseBuzz and BillDesk working simultaneously
- Webhook handlers implemented
- Comprehensive documentation added

---

💳 BillDesk Test Card Credentials
Credit Card (Successful Transaction)
Card Number: 4111111111111111 (Visa)
Cardholder Name: Test User (any name)
Expiry Date: Any future date (e.g., 12/25)
CVV: 123
Alternative Test Cards
Mastercard:

Card Number: 5123456789012346
CVV: 123
Expiry: Any future date
Visa:

Card Number: 4012001037141112
CVV: 123
Expiry: Any future date
Test Cards for Specific Scenarios
For Failed Transaction:

Card Number: 4000000000000002
CVV: 123
Expiry: Any future date
For Insufficient Funds:

Card Number: 4000000000000010
CVV: 123
Expiry: Any future date
Net Banking Test Credentials
Most UAT environments use:

Username: test or testuser
Password: test123 or password
OTP: 123456 (if required)
UPI Test
UPI ID: test@upi or as provided by BillDesk
UPI PIN: 1234

**Last Updated**: May 13, 2026
**Integration Version**: 1.0.0
**Status**: Ready for Testing
