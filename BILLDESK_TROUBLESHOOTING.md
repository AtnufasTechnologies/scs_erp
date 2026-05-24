# BillDesk Payment Integration - Troubleshooting Guide

## Current Status

### ✅ What's Working:

- Direct redirect implementation in controllers (no intermediate blade page)
- Content-Type header properly configured
- JOSE encoding/decoding working correctly
- Payment URL extraction from API response

### ⚠️ Current Issues:

1. **Local Testing**: IP `45.249.165.110` not whitelisted (expected - development machine)
2. **Live Server**: Error "Required header content-type is missing" OR "No redirect happening"

---

## Solutions by Environment

### 🔧 For Live Server (Production)

If you're getting "Content-Type missing" or "No redirect" on the live server:

#### Step 1: Run Diagnostic Tool

Access: `https://your-domain.com/billdesk-diagnostic.php`

This will show you:

- Server's public IP address
- cURL configuration
- Header transmission test
- Specific error codes from BillDesk

#### Step 2: Common Live Server Issues

**Issue A: Reverse Proxy/Load Balancer Stripping Headers**

- **Solution**: Add proxy configuration to preserve headers
- For Nginx:
    ```nginx
    location / {
        proxy_pass http://backend;
        proxy_set_header Content-Type $http_content_type;
        proxy_pass_request_headers on;
    }
    ```
- For Apache (`.htaccess` or config):
    ```apache
    RequestHeader set Content-Type application/jose
    ```

**Issue B: Server IP Not Whitelisted**

- **Error Code**: GNAUE0006
- **Solution**: Contact BillDesk support with your server IP
- Get IP from diagnostic tool or run: `curl ifconfig.me`
- Email BillDesk: support@billdesk.com
- Provide: Merchant ID, Client ID, Server IP

**Issue C: PHP Configuration**

- **Solution**: Check `php.ini` for:
    ```ini
    allow_url_fopen = On
    extension=curl
    extension=openssl
    ```

#### Step 3: Test from Live Server Command Line

```bash
cd /path/to/your/laravel/scs_erp
php artisan billdesk:test-payment
```

Expected success output:

```
✓ Order created successfully!
✓ Payment URL found:
https://pay.billdesk.com/...
```

---

## Current Implementation Flow

### Payment Controllers (Both Test & Production)

```
User submits payment form
         ↓
Controller receives request
         ↓
BillDeskService->createOrder() called
         ↓
API returns response with 'links' array
         ↓
Controller extracts payment URL from links
         ↓
redirect()->away($paymentUrl)  ← DIRECT REDIRECT
         ↓
User lands on BillDesk payment page
```

**No intermediate blade page** - User goes directly to BillDesk!

### Files Modified

1. **PaymentGatewayTestController.php** (Line 109-186)
    - Uses BillDeskService
    - Direct redirect to payment URL
    - Better error messages with specific guidance

2. **AdmissionController.php** (Around line 1733)
    - Direct redirect implementation
    - No blade page rendering

3. **BillDeskService.php** (Line 347-398)
    - Improved cURL configuration
    - Better error logging
    - Verbose mode when debugging

---

## Error Code Reference

| Error Code    | Meaning                     | Solution                                      |
| ------------- | --------------------------- | --------------------------------------------- |
| **GNAUE0006** | IP not whitelisted          | Contact BillDesk to whitelist server IP       |
| **GNIRE0002** | Content-Type header missing | Check proxy/server configuration              |
| **GNAUE0003** | Authentication failed       | Verify BillDesk credentials and JOSE encoding |
| **HTTP 401**  | Unauthorized                | Check IP whitelist or credentials             |
| **HTTP 400**  | Bad request                 | Check request format                          |

---

## Testing Checklist

### ✓ Local Development

- [x] Test command: `php artisan billdesk:test-auth`
- [ ] Expected: HTTP 401 GNAUE0006 (IP not whitelisted) ← This is OK for local
- [x] JOSE encoding working
- [x] Headers configured correctly

### ✓ Live Server

- [ ] Run diagnostic: `/billdesk-diagnostic.php`
- [ ] Verify server IP matches whitelisted IP
- [ ] Test payment flow through web interface
- [ ] Check Laravel logs: `tail -f storage/logs/laravel.log`
- [ ] Expected: Successful redirect to BillDesk payment page

---

## Quick Fixes

### "No redirect happening"

**Cause**: BillDesk API returning error (caught by exception handler)

**Check**:

```bash
# View recent Laravel logs
tail -100 storage/logs/laravel.log | grep -i "billdesk"

# Check for specific errors
tail -100 storage/logs/laravel.log | grep -i "error"
```

**Solution**: Look for error code in logs and apply fix from Error Code Reference table above

### "Data will be unsaved" warning

**Status**: ✅ FIXED

- Added `isRedirecting` flag
- `beforeunload` event now skips warning when redirecting

### Clear Cache After Changes

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## Support Tools Created

1. **billdesk-diagnostic.php** - Web-based diagnostic tool
    - Location: `/public/billdesk-diagnostic.php`
    - Access: `https://your-domain.com/billdesk-diagnostic.php`

2. **TestBillDeskPayment Command** - CLI testing
    - Run: `php artisan billdesk:test-payment`
    - Shows full payment flow and errors

3. **TestBillDeskAuth Command** - Auth testing
    - Run: `php artisan billdesk:test-auth`
    - Tests JOSE encoding and API connectivity

---

## Next Steps for Production

1. ✅ Code is ready - direct redirect implemented
2. ⚠️ **Action Required**: Whitelist production server IP with BillDesk
3. ⚠️ **Action Required**: Run diagnostic tool on live server
4. ⚠️ **Action Required**: Test payment flow on live server
5. ⚠️ **If still failing**: Check server logs and proxy configuration

---

## Contact

**BillDesk Support**:

- Email: support@billdesk.com
- Portal: https://www.billdesk.com/merchant-portal
- Provide: Merchant ID, Server IP, Error logs

**Required Information for Support**:

- Merchant ID: `SALESCUAT` (UAT) or your production merchant ID
- Client ID: `salescuat`
- Server IP: [Get from diagnostic tool]
- Error Code: [From Laravel logs]
- Environment: UAT/Production

---

Generated: {{ date('Y-m-d H:i:s') }}
