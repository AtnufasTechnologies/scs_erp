# BillDesk Integration Guide - Shell & Laravel

**Based on Official Documentation:** https://docs.billdesk.io/reference/createorder

This guide provides complete information on integrating BillDesk payment gateway using Shell commands (cURL) and Laravel.

---

## Table of Contents

1. [API Overview](#api-overview)
2. [Authentication](#authentication)
3. [Shell/cURL Integration](#shellcurl-integration)
4. [Laravel Integration](#laravel-integration)
5. [API Reference](#api-reference)
6. [Testing](#testing)
7. [Production Deployment](#production-deployment)

---

## API Overview

### Base URLs

- **UAT (Testing):** `https://uat1.billdesk.com/u2/payments/ve1_2`
- **Production:** `https://api.billdesk.com/payments/ve1_2`

### Key Features

- JWT-based authentication (HMAC-SHA256)
- Idempotency support via BD-Traceid
- Secure encrypted requests and responses
- Support for multiple payment methods
- Webhook support for server-to-server notifications

---

## Authentication

BillDesk uses JWT (JSON Web Token) for authentication with HMAC-SHA256 algorithm.

### Required Credentials

```bash
MERCHANT_ID="your_merchant_id"      # Provided by BillDesk
CLIENT_ID="your_client_id"          # Provided by BillDesk
SECRET_KEY="your_secret_key"        # Provided by BillDesk
```

### JWT Structure

**Header:**

```json
{
    "alg": "HS256",
    "clientid": "your_client_id"
}
```

**Payload:** Contains the request body (order details)

**Signature:** HMAC-SHA256(base64UrlEncode(header) + "." + base64UrlEncode(payload), secret_key)

---

## Shell/cURL Integration

### Prerequisites

Install required tools for JWT generation:

**Option 1: Python**

```bash
pip3 install pyjwt requests pytz
```

**Option 2: Node.js**

```bash
npm install -g jsonwebtoken
```

**Option 3: jwt-cli**

```bash
npm install -g jwt-cli
```

### Complete Shell Script

See [`billdesk-shell-example.sh`](./billdesk-shell-example.sh) for a complete working example.

### Basic cURL Request Structure

```bash
#!/bin/bash

# Configuration
API_URL="https://uat1.billdesk.com/u2/payments/ve1_2/orders/create"
TRACE_ID="$(date +%s)$(shuf -i 100000000000-999999999999 -n 1)"
TIMESTAMP=$(date +"%Y%m%d%H%M%S")

# Generate JWT token (using Python example)
JWT_TOKEN=$(python3 -c "
import jwt
payload = {
    'mercid': 'your_merchant_id',
    'orderid': 'ORDER123456',
    'amount': '100.00',
    'order_date': '$(date -u +"%Y-%m-%dT%H:%M:%S%z")',
    'currency': '356',
    'ru': 'https://your-domain.com/response',
    'itemcode': 'DIRECT'
}
headers = {'alg': 'HS256', 'clientid': 'your_client_id'}
print(jwt.encode(payload, 'your_secret_key', algorithm='HS256', headers=headers))
")

# Make API call
curl --request POST \
  --url "$API_URL" \
  --header "Content-Type: application/jose" \
  --header "Accept: application/jose" \
  --header "BD-Traceid: $TRACE_ID" \
  --header "BD-Timestamp: $TIMESTAMP" \
  --data "$JWT_TOKEN"
```

### Python Example (Recommended for Shell Scripts)

```python
#!/usr/bin/env python3

import jwt
import json
import requests
from datetime import datetime
import random

# Configuration
MERCHANT_ID = "your_merchant_id"
CLIENT_ID = "your_client_id"
SECRET_KEY = "your_secret_key"
API_URL = "https://uat1.billdesk.com/u2/payments/ve1_2/orders/create"

# Generate unique identifiers
order_id = f"ORDER{int(datetime.now().timestamp())}{random.randint(1000, 9999)}"
trace_id = str(int(datetime.now().timestamp())) + str(random.randint(100000000000, 999999999999))
timestamp = datetime.now().strftime("%Y%m%d%H%M%S")
order_date = datetime.now().isoformat()

# Prepare payload
payload = {
    "mercid": MERCHANT_ID,
    "orderid": order_id,
    "amount": "100.00",
    "order_date": order_date,
    "currency": "356",  # INR
    "ru": "https://your-domain.com/payment-response",
    "itemcode": "DIRECT",
    "customer": {
        "email_id": "customer@example.com",
        "mobile_no": "9876543210"
    },
    "device": {
        "init_channel": "internet",
        "ip": "192.168.1.1",
        "user_agent": "Mozilla/5.0"
    }
}

# Encode JWT
jwt_headers = {"alg": "HS256", "clientid": CLIENT_ID}
jwt_token = jwt.encode(payload, SECRET_KEY, algorithm="HS256", headers=jwt_headers)

# Make API call
headers = {
    "Content-Type": "application/jose",
    "Accept": "application/jose",
    "BD-Traceid": trace_id,
    "BD-Timestamp": timestamp
}

response = requests.post(API_URL, data=jwt_token, headers=headers)

# Decode response
if response.status_code == 200:
    response_data = jwt.decode(response.text, SECRET_KEY, algorithms=["HS256"])
    print(json.dumps(response_data, indent=2))
else:
    print(f"Error: {response.status_code}")
    print(response.text)
```

---

## Laravel Integration

### Service Class

The `BillDeskService` class handles all BillDesk API interactions.

**Location:** `app/Services/BillDeskService.php`

### Usage Example

```php
<?php

use App\Services\BillDeskService;

// Initialize service
$billDesk = new BillDeskService();

// Create order
$result = $billDesk->createOrder(
    orderId: 'ORDER' . time() . rand(1000, 9999),
    amount: 100.00,
    customerName: 'John Doe',
    returnUrl: route('payment.response'),
    additionalInfo: [
        'info1' => 'Application Fee',
        'info3' => 'Student ID: 12345'
    ],
    customerInfo: [
        'email' => 'student@example.com',
        'mobile' => '9876543210'
    ]
);

if ($result['success']) {
    // Order created successfully
    $bdOrderId = $result['bdOrderId'];
    $authToken = $result['authToken'];
    $merchantId = $result['merchantId'];

    // Redirect to BillDesk payment page
    return view('payment.billdesk', [
        'bdOrderId' => $bdOrderId,
        'authToken' => $authToken,
        'merchantId' => $merchantId
    ]);
} else {
    // Handle error
    $error = $result['error'];
    return back()->with('error', $error);
}
```

### Verify Transaction

```php
// Verify transaction status
$verification = $billDesk->verifyTransaction($orderId);

if ($verification['success']) {
    $status = $verification['transaction_status'];
    $amount = $verification['amount'];

    if ($status === 'success') {
        // Payment successful
    }
}
```

### Handle Webhook Response

```php
public function webhookHandler(Request $request)
{
    $billDesk = new BillDeskService();

    // Get JWT response from BillDesk
    $jwtResponse = $request->input('transaction_response');

    // Parse and verify
    $result = $billDesk->parseWebhookResponse($jwtResponse);

    if ($result['success']) {
        $orderId = $result['orderid'];
        $status = $result['transaction_status'];
        $amount = $result['amount'];

        // Update your database
        // Process the payment status

        return response()->json(['status' => 'success']);
    }

    return response()->json(['status' => 'error'], 400);
}
```

---

## API Reference

### Create Order API

**Endpoint:** `POST /orders/create`

**Headers:**
| Header | Type | Required | Description |
|--------|------|----------|-------------|
| Content-Type | string | Yes | Must be `application/jose` |
| Accept | string | Yes | Must be `application/jose` |
| BD-Traceid | string | Yes | Unique identifier for idempotency (24h window) |
| BD-Timestamp | string | Yes | Format: `YYYYMMDDHHmmss` |

**Request Body (JWT Payload):**

| Field               | Type   | Required    | Description                               |
| ------------------- | ------ | ----------- | ----------------------------------------- |
| mercid              | string | Yes         | Merchant ID from BillDesk                 |
| orderid             | string | Yes         | Unique order ID generated by merchant     |
| order_date          | string | Yes         | ISO 8601 format: `YYYY-MM-DDThh:mm:ssTZD` |
| amount              | string | Yes         | Amount with 2 decimals (e.g., "299.28")   |
| currency            | string | Yes         | ISO currency code (356 for INR)           |
| ru                  | string | Yes         | Return URL after payment                  |
| itemcode            | string | Yes         | Default: "DIRECT"                         |
| customer            | object | Recommended | Customer details                          |
| customer.email_id   | string | Optional    | Customer email                            |
| customer.mobile_no  | string | Optional    | Customer mobile number                    |
| device              | object | Required    | Device information                        |
| device.init_channel | string | Required    | "internet", "mobile", "app"               |
| device.ip           | string | Required    | Customer IP address                       |
| device.user_agent   | string | Required    | Browser user agent                        |
| additional_info     | object | Optional    | Up to 7 additional fields                 |

**Response (Success - HTTP 200):**

```json
{
    "status": "ACTIVE",
    "orderid": "ORDER123456",
    "bdorderid": "BD1234567890",
    "mercid": "MERCHANT123",
    "order_date": "2026-05-14T10:30:00+05:30",
    "amount": "100.00",
    "currency": "356",
    "links": [
        {
            "rel": "self",
            "href": "https://uat1.billdesk.com/...",
            "method": "GET"
        },
        {
            "rel": "payment",
            "href": "https://uat1.billdesk.com/...",
            "method": "POST",
            "headers": {
                "authorization": "Bearer token_here"
            }
        }
    ]
}
```

**Response (Error):**

```json
{
    "error_code": "invalid_request",
    "error_description": "Invalid merchant configuration",
    "status": "FAILED"
}
```

### HTTP Status Codes

| Code | Description                       |
| ---- | --------------------------------- |
| 200  | OK - Request successful           |
| 403  | Forbidden - Authentication failed |
| 500  | Internal Server Error             |

---

## Testing

### Test Environment

Use UAT credentials and URL for testing:

```bash
BILLDESK_API_URL=https://uat1.billdesk.com/u2/payments/ve1_2/orders/create
```

### Laravel Test Route

Access the test page:

```
http://your-domain/payment-gateway-test
```

### Shell Script Testing

Run the example script:

```bash
chmod +x billdesk-shell-example.sh
./billdesk-shell-example.sh
```

Or use Python:

```bash
python3 /tmp/billdesk_create_order.py
```

### Test Cards

Refer to BillDesk UAT documentation for test card numbers and credentials.

---

## Production Deployment

### 1. Update Environment Variables

```bash
# Production URL
BILLDESK_API_URL=https://api.billdesk.com/payments/ve1_2/orders/create

# Production credentials (get from BillDesk)
BILLDESK_MERCHANT_ID=your_production_merchant_id
BILLDESK_CLIENT_ID=your_production_client_id
BILLDESK_SECRET_KEY=your_production_secret_key
BILLDESK_IP_ADDRESS=your_production_server_ip
```

### 2. Configure Webhooks

Set webhook URL in BillDesk dashboard:

```
https://your-domain.com/payment-webhook-billdesk
```

### 3. SSL Certificate

Ensure your domain has a valid SSL certificate (required for production).

### 4. IP Whitelisting

Whitelist your server IP in BillDesk dashboard if required.

### 5. Logging and Monitoring

- Enable transaction logging
- Monitor failed transactions
- Set up alerts for payment failures
- Track webhook responses

---

## Key Differences from Other Gateways

### vs. Razorpay / Stripe

- Uses JWT instead of simple API keys
- All requests/responses are JWT encoded
- Requires BD-Traceid for idempotency
- Special timestamp format (YYYYMMDDHHmmss)

### vs. EaseBuzz

- More secure with JWT encryption
- Requires HMAC-SHA256 signing
- Stricter idempotency checks
- More detailed device information required

---

## Common Issues and Solutions

### Issue 1: JWT Decode Error

**Solution:** Ensure secret key matches exactly, check algorithm is HS256

### Issue 2: Invalid Timestamp

**Solution:** Use format YYYYMMDDHHmmss (e.g., 20260514143000)

### Issue 3: Duplicate BD-Traceid

**Solution:** Ensure unique trace ID for each request (24h window)

### Issue 4: Order Date Format Error

**Solution:** Use ISO 8601 format: `2026-05-14T14:30:00+05:30`

### Issue 5: Amount Format Error

**Solution:** Always use 2 decimal places: "100.00" not "100"

---

## Security Best Practices

1. **Never expose secret key** in client-side code
2. **Validate webhook signatures** using JWT decode
3. **Use HTTPS** for all communications
4. **Store credentials securely** in environment variables
5. **Implement request logging** for audit trails
6. **Verify transaction status** via API before marking as paid
7. **Use unique order IDs** to prevent duplicates
8. **Set appropriate timeouts** for cURL requests
9. **Implement retry logic** with exponential backoff
10. **Monitor for failed transactions** and alerts

---

## Additional Resources

- **Official Documentation:** https://docs.billdesk.io/
- **API Reference:** https://docs.billdesk.io/reference/createorder
- **Support:** Contact BillDesk support for technical assistance
- **Laravel Service:** `app/Services/BillDeskService.php`
- **Shell Example:** `billdesk-shell-example.sh`

---

## Summary

The BillDesk integration supports both Shell/cURL and Laravel implementations:

- **Shell/cURL:** Use Python/Node.js for JWT encoding, then make cURL requests
- **Laravel:** Use the `BillDeskService` class which handles JWT automatically
- **Authentication:** JWT with HMAC-SHA256
- **Format:** All requests/responses use `application/jose` content type
- **Idempotency:** BD-Traceid ensures requests aren't duplicated within 24 hours

For most Laravel applications, use the BillDeskService class rather than direct cURL calls.

---

**Last Updated:** May 14, 2026  
**API Version:** ve1_2  
**Status:** Production Ready ✓
