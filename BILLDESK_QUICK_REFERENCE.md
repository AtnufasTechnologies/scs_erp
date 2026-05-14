# BillDesk Integration - Quick Reference

**Official API Documentation:** https://docs.billdesk.io/reference/createorder

## Files Overview

| File                                                                           | Purpose                                        |
| ------------------------------------------------------------------------------ | ---------------------------------------------- |
| [BillDeskService.php](app/Services/BillDeskService.php)                        | Laravel service class for BillDesk integration |
| [BILLDESK_SHELL_INTEGRATION_GUIDE.md](BILLDESK_SHELL_INTEGRATION_GUIDE.md)     | Complete Shell/cURL integration guide          |
| [BILLDESK_INTEGRATION_DOCUMENTATION.md](BILLDESK_INTEGRATION_DOCUMENTATION.md) | Laravel integration documentation              |
| [billdesk-shell-example.sh](billdesk-shell-example.sh)                         | Executable Shell script with examples          |
| [.env.example](.env.example)                                                   | Environment configuration template             |

## Quick Start

### 1. Configuration

Update your `.env` file:

```bash
BILLDESK_MERCHANT_ID=your_merchant_id
BILLDESK_CLIENT_ID=your_client_id
BILLDESK_SECRET_KEY=your_secret_key
BILLDESK_API_URL=https://uat1.billdesk.com/u2/payments/ve1_2/orders/create
BILLDESK_IP_ADDRESS=your_server_ip
```

### 2. Laravel Usage

```php
use App\Services\BillDeskService;

$billDesk = new BillDeskService();

$result = $billDesk->createOrder(
    orderId: 'ORDER' . time(),
    amount: 100.00,
    customerName: 'John Doe',
    returnUrl: route('payment.response'),
    customerInfo: [
        'email' => 'customer@example.com',
        'mobile' => '9876543210'
    ]
);

if ($result['success']) {
    // Redirect to payment page with $result['authToken']
}
```

### 3. Shell/cURL Usage

```bash
# Run the example script
chmod +x billdesk-shell-example.sh
./billdesk-shell-example.sh

# Or use Python
python3 /tmp/billdesk_create_order.py
```

## API Endpoints

| Environment   | Base URL                                      |
| ------------- | --------------------------------------------- |
| UAT (Testing) | `https://uat1.billdesk.com/u2/payments/ve1_2` |
| Production    | `https://api.billdesk.com/payments/ve1_2`     |

## Key API Operations

### Create Order

```
POST /orders/create
Content-Type: application/jose
Accept: application/jose
BD-Traceid: {unique_id}
BD-Timestamp: {YYYYMMDDHHmmss}
```

### Retrieve Transaction

```
POST /transactions/get
Content-Type: application/jose
Accept: application/jose
```

## Important Notes

1. **JWT Encoding Required:** All requests and responses use JWT with HMAC-SHA256
2. **Content Type:** Must be `application/jose`
3. **Idempotency:** BD-Traceid must be unique within 24 hours
4. **Amount Format:** Always use 2 decimal places (e.g., "100.00")
5. **Currency Code:** 356 for INR (ISO 4217)
6. **Order Date:** ISO 8601 format with timezone

## Testing

Access the test interface:

```
http://your-domain/payment-gateway-test
```

## Service Methods

| Method                   | Description                   |
| ------------------------ | ----------------------------- |
| `createOrder()`          | Create new payment order      |
| `verifyTransaction()`    | Verify transaction status     |
| `parseWebhookResponse()` | Parse webhook JWT response    |
| `generateUniqueNum()`    | Generate unique order numbers |

## Response Handling

### Successful Order Creation

```json
{
  "success": true,
  "bdOrderId": "BD1234567890",
  "authToken": "Bearer token...",
  "merchantId": "MERCHANT123",
  "returnUrl": "https://...",
  "links": [...]
}
```

### Error Response

```json
{
    "success": false,
    "error": "Error description",
    "error_code": "error_code"
}
```

## Webhook Integration

```php
public function webhookHandler(Request $request)
{
    $billDesk = new BillDeskService();
    $jwtResponse = $request->input('transaction_response');

    $result = $billDesk->parseWebhookResponse($jwtResponse);

    if ($result['success']) {
        // Process payment: $result['transaction_status']
    }
}
```

## Security Checklist

- ✓ Secret key stored in environment variables
- ✓ HTTPS enabled for production
- ✓ Webhook signature verification
- ✓ Unique order IDs
- ✓ Transaction verification before confirmation
- ✓ Proper error logging
- ✓ IP whitelisting (if required)

## Common Issues

| Issue               | Solution                                |
| ------------------- | --------------------------------------- |
| JWT decode error    | Verify secret key and algorithm (HS256) |
| Invalid timestamp   | Use format: YYYYMMDDHHmmss              |
| Duplicate trace ID  | Generate unique ID for each request     |
| Amount format error | Use 2 decimal places: "100.00"          |

## Support & Documentation

- **Full Laravel Docs:** [BILLDESK_INTEGRATION_DOCUMENTATION.md](BILLDESK_INTEGRATION_DOCUMENTATION.md)
- **Shell/cURL Guide:** [BILLDESK_SHELL_INTEGRATION_GUIDE.md](BILLDESK_SHELL_INTEGRATION_GUIDE.md)
- **Official API Docs:** https://docs.billdesk.io/
- **API Reference:** https://docs.billdesk.io/reference/createorder

## Version Information

- **API Version:** ve1_2
- **Integration Date:** May 13, 2026
- **Last Updated:** May 14, 2026
- **Laravel Version:** Compatible with Laravel 8+
- **Status:** Production Ready ✓

---

**Need Help?**

1. Check the [Shell Integration Guide](BILLDESK_SHELL_INTEGRATION_GUIDE.md) for Shell/cURL examples
2. Review the [Full Documentation](BILLDESK_INTEGRATION_DOCUMENTATION.md) for Laravel implementation
3. Run the test script: `./billdesk-shell-example.sh`
4. Access test page: `/payment-gateway-test`
