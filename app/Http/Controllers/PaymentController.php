<?php

namespace App\Http\Controllers;

use App\Models\AdmissionApplication;
use App\Models\StudentPayment;
use Illuminate\Http\Request;

use Carbon\Carbon;
use Easebuzz\PayWithEasebuzzLaravel\Lib\EasebuzzLib\Easebuzz;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    private $easebuzz;

    public function __construct()
    {
        $MERCHANT_KEY = env('EASEBUZZ_KEY');
        $SALT = env('EASEBUZZ_SALT');
        $ENV = env('EASEBUZZ_ENV'); // "test" or "prod"

        $this->easebuzz = new Easebuzz($MERCHANT_KEY, $SALT, $ENV);
    }

    /**
     * Webhook: Easebuzz server->server notifications
     */
    public function webhook(Request $request)
    {
        // Validate signature if Easebuzz sends one (check docs)
        // Example: $signature = $request->header('X-Easebuzz-Signature'); verify it
        $payload = $request->all();
        $txnid = $payload['txnid'] ?? null;

        if (!$txnid) {
            return response()->json(['status' => 'error', 'message' => 'txnid missing'], 400);
        }

        $payment = StudentPayment::where('txnid', $txnid)->first();

        if (!$payment) {
            // maybe log and create a record
            Log::warning('Easebuzz webhook for unknown txn: ' . $txnid, $payload);
            return response()->json(['status' => 'ok']);
        }

        // Update according to webhook payload status
        $status = $payload['status'] ?? 'pending';
        $payment->update([
            'status' => strtoupper($status),
            'raw_response' => json_encode($payload)
        ]);

        // perform reconciliation, ledger updates etc.

        return response()->json(['status' => 'ok']);
    }
}
