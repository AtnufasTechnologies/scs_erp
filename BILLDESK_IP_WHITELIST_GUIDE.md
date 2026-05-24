# BillDesk IP Whitelisting Guide

## Issue

When testing BillDesk payments, you may encounter the error:

```json
{
    "status": 401,
    "error_type": "authentication_error",
    "error_code": "GNAUE0006",
    "message": "Request from unauthorized ip"
}
```

## Root Cause

BillDesk requires all server IPs making API requests to be whitelisted for security. This is a mandatory step before going live.

## Solution

### Step 1: Identify Your Server IPs

#### For Production Server:

```bash
# SSH into your production server and run:
curl -s ifconfig.me
```

#### For Local/Development:

```bash
curl -s ifconfig.me
```

### Step 2: Contact BillDesk Support

Email BillDesk support with the following information:

**Subject:** IP Whitelist Request for Merchant [YOUR_MERCHANT_ID]

**Body:**

```
Dear BillDesk Support,

We request to whitelist the following IP addresses for our merchant account:

Merchant ID: [YOUR_MERCHANT_ID]
Client ID: [YOUR_CLIENT_ID]

IP Addresses to Whitelist:
- Production: xxx.xxx.xxx.xxx
- UAT/Testing: xxx.xxx.xxx.xxx (if applicable)

Please confirm once the IPs have been whitelisted.

Thank you.
```

### Step 3: Update .env Configuration

Add your server's public IP to `.env`:

```env
BILLDESK_IP_ADDRESS=your_production_server_ip
```

**Example:**

```env
BILLDESK_IP_ADDRESS=149.28.139.96
```

### Step 4: Verify Configuration

Run the authentication test:

```bash
php artisan billdesk:test-auth
```

**Expected Success Response:**

```json
{
    "status": "ACTIVE",
    "orderid": "TEST...",
    "bdorderid": "...",
    "links": [...]
}
```

## Important Notes

1. **Production vs Development IPs:**
    - You need separate whitelisting for UAT (testing) and Production environments
    - UAT URL: `https://uat1.billdesk.com/...`
    - Production URL: `https://api.billdesk.com/...`

2. **Multiple Servers:**
    - If you have multiple application servers (load balanced), whitelist all IPs
    - All servers in your infrastructure making API calls need whitelisting

3. **Dynamic IPs:**
    - If your server has dynamic IP, request a static IP from your hosting provider
    - BillDesk cannot whitelist dynamic IP ranges

4. **VPN/Proxy:**
    - If using VPN or proxy, whitelist the exit IP, not your local IP
    - The IP BillDesk sees must be whitelisted

## Troubleshooting

### Check Your Current Public IP

```bash
curl -s ifconfig.me
# or
curl -s icanhazip.com
# or
dig +short myip.opendns.com @resolver1.opendns.com
```

### Check Laravel Logs

```bash
tail -f storage/logs/laravel.log | grep BillDesk
```

### Test After Whitelisting

```bash
php artisan billdesk:test-auth
```

## Alternative: Direct Redirect Method

While waiting for IP whitelisting, you can temporarily use direct redirect to BillDesk payment page (already implemented in the current blade file):

1. The blade file now supports direct redirect via the `paymentUrl` from the API response
2. If the `links` array contains a GET URL, it will redirect directly
3. This bypasses the SDK loading issues

The current implementation automatically:

- Tries direct payment URL first
- Falls back to links array GET method
- Shows clear error messages if URL is not available

## Contact Information

**BillDesk Support:**

- Email: support@billdesk.com
- Phone: Check your merchant agreement
- Portal: https://www.billdesk.com/merchant-portal

## Current Environment Status

**Local Machine:**

- Current IP: 45.249.165.110
- Status: ⚠️ Needs whitelisting for UAT

**Production Server:**

- Configured IP: 149.28.139.96
- Status: ⚠️ Verify with BillDesk

---

**Last Updated:** {{ date('Y-m-d H:i:s') }}
