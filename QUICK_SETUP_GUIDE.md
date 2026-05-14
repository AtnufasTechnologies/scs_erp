# Quick Setup Guide - BillDesk Payment Gateway Integration

## 🚀 Quick Start (5 Minutes)

### Step 1: Update Environment Variables

Edit your `.env` file and add BillDesk credentials:

```bash
# BillDesk Payment Gateway Configuration
BILLDESK_MERCHANT_ID=your_merchant_id_here
BILLDESK_CLIENT_ID=your_client_id_here
BILLDESK_SECRET_KEY=your_secret_key_here
BILLDESK_API_URL=https://uat.billdesk.com/payments/ve1_2/orders/create
BILLDESK_IP_ADDRESS=your_server_ip_here
```

### Step 2: Add Gateway Logos (Optional)

Place logo images in `public/admin/images/`:

- `billdesk.jpg` - BillDesk logo image
- `easebuzz.jpg` - EaseBuzz logo image (if not already present)

### Step 3: Clear Laravel Cache

```bash
php artisan config:cache
php artisan route:cache
php artisan cache:clear
```

### Step 4: Test the Integration

Navigate to the test page:

```
http://your-domain/payment-gateway-test
```

## ✅ What's Been Integrated

### Files Created:

1. ✅ BillDesk Service (`app/Services/BillDeskService.php`)
2. ✅ JWT Library Files (`app/Services/BillDesk/JWT/`)
3. ✅ Test Controller (`app/Http/Controllers/PaymentGatewayTestController.php`)
4. ✅ Payment Views (billdesk-payment.blade.php, payment-gateway-test.blade.php)
5. ✅ Test Result View (payment-test-result.blade.php)
6. ✅ Documentation (BILLDESK_INTEGRATION_DOCUMENTATION.md)

### Files Modified:

1. ✅ AdmissionController (added BillDesk methods)
2. ✅ Routes (added BillDesk and test routes)
3. ✅ Payment Checkout View (enabled BillDesk option)
4. ✅ Environment Example (.env.example)

## 🧪 Testing

### Test Both Gateways:

1. **Access Test Page**: `http://your-domain/payment-gateway-test`
2. **Select Gateway**: Choose EaseBuzz or BillDesk
3. **Enter Test Data**:
    - Amount: 1 (or any test amount)
    - Name: Test Customer
    - Email: test@example.com
    - Phone: 9999999999
4. **Initiate Payment**: Click the button
5. **Complete Payment**: Follow gateway instructions
6. **View Results**: Check the result page

### Test Real Admission Flow:

1. Go to admission application
2. Fill and submit application form
3. On payment checkout, select BillDesk
4. Complete payment
5. Verify success page and email/SMS

## 📋 Key Routes

### Production Routes:

- **Admission Payment**: `/admission/payment-checkout`
- **BillDesk Response**: `/admission/payment-billdesk-response`
- **BillDesk Webhook**: `/admission/payment-webhook-billdesk`

### Test Routes:

- **Test Page**: `/payment-gateway-test`
- **Test Response**: `/payment-test-billdesk-response`

## 🔧 Configuration

### For UAT/Testing:

```bash
BILLDESK_API_URL=https://uat.billdesk.com/payments/ve1_2/orders/create
```

### For Production:

```bash
BILLDESK_API_URL=https://api.billdesk.com/payments/ve1_2/orders/create
```

## 📞 Webhook Configuration

Configure these webhook URLs in your BillDesk merchant dashboard:

**BillDesk Webhook URL:**

```
https://your-domain.com/admission/payment-webhook-billdesk
```

**Return URL** (automatically configured):

```
https://your-domain.com/admission/payment-billdesk-response
```

## 🎯 Features Implemented

✅ BillDesk payment initiation
✅ JWT-based secure authentication
✅ Order creation and tracking
✅ Payment response handling
✅ Webhook integration
✅ Email notifications
✅ SMS notifications
✅ Database logging
✅ Error handling
✅ Test page with both gateways
✅ Payment gateway selection
✅ Comprehensive documentation

## 🔐 Security

- ✅ JWT encryption (HS256)
- ✅ Secret key authentication
- ✅ HTTPS communication
- ✅ IP whitelisting
- ✅ Hash verification

## 📊 Database Changes

No database migrations required! Uses existing payment tables:

- `admission_applications` (gateway_type field)
- `admission_application_payment_logs`

## 🐛 Troubleshooting

### Payment Not Initiating?

1. Check `.env` variables are set correctly
2. Verify merchant credentials with BillDesk
3. Check server IP is whitelisted
4. Review Laravel logs: `storage/logs/laravel.log`

### Response Not Received?

1. Verify return URL is accessible from internet
2. Check firewall settings
3. Test webhook URL manually
4. Check BillDesk transaction dashboard

### JWT Errors?

1. Verify `BILLDESK_SECRET_KEY` is correct
2. Check JWT library files exist in `app/Services/BillDesk/JWT/`
3. Ensure proper file permissions

## 📝 Next Steps

1. **Get BillDesk Credentials**: Contact BillDesk for UAT credentials
2. **Update .env**: Add credentials to `.env` file
3. **Test**: Use test page to verify integration
4. **Configure Webhooks**: Set webhook URL in BillDesk dashboard
5. **Production**: Switch to production URLs when ready

## 💡 Tips

- Start with UAT/test environment
- Test with small amounts (₹1-10)
- Monitor Laravel logs during testing
- Keep credentials secure (never commit to git)
- Use different credentials for UAT and production

## 📚 Documentation

Full documentation available in:

- `BILLDESK_INTEGRATION_DOCUMENTATION.md`

## ✨ Quick Test Command

Test that all routes are registered:

```bash
php artisan route:list | grep -i payment
```

## 🎉 You're Ready!

The BillDesk payment gateway is now fully integrated and ready for testing!

Access the test page at: **`http://your-domain/payment-gateway-test`**

---

**Need Help?**

- Check full documentation: `BILLDESK_INTEGRATION_DOCUMENTATION.md`
- Review Laravel logs: `storage/logs/laravel.log`
- Test with small amounts first
