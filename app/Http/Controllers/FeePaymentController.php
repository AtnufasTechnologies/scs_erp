<?php

namespace App\Http\Controllers;

use App\Models\FailedTransaction;
use App\Models\FailedTransactionLog;
use App\Models\FeesStructure;
use App\Models\FeeStructureHasHead;
use App\Models\FeeStructureHasManyProgram;
use App\Models\PaymentGatewayType;
use App\Models\StudentMaster;
use App\Models\StudentPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Easebuzz\PayWithEasebuzzLaravel\Lib\EasebuzzLib\Easebuzz;

class FeePaymentController extends Controller
{
    function index(Request $request)
    {
        // ---- Base Query ----
        $query = StudentMaster::with([
            'batchmaster',
            'programgroup.programInfo',
            'stdfeestructure.feeHeads',
            'stdfeestructure',
            'stdfeestructure.programspivot',
            'feepayment'
        ]);

        // ---- Filters ----

        if ($request->roll_no) {
            $searchValues = preg_split('/\s+/', $request->roll_no, -1, PREG_SPLIT_NO_EMPTY);
            $query->where(function ($q) use ($searchValues) {
                foreach ($searchValues as $value) {
                    $q->orWhere('roll_no', 'LIKE', "%$value%");
                }
            });
        }

        if ($request->filter_batch) {
            $query->where('batch', $request->filter_batch);
        }

        if ($request->filter_pgr) {
            $query->where('programme', $request->filter_pgr);
        }

        // ---- PAGINATION ----
        $data = $query->paginate(20); // <<<<<< THIS IS THE KEY

        // ---- TRANSFORM EACH RECORD USING through() ----
        $students = $data->through(function ($student) {

            $applicableFS = FeesStructure::where('batch_id', $student->batch)
                ->whereHas('programspivot', function ($q) use ($student) {
                    $q->where('std_program_id', $student->programme);
                })
                ->whereIn('std_current_year', range(1, $student->current_year))
                ->get();

            $fsWithStatus = $applicableFS->map(function ($fs) use ($student) {

                $payment = $student->feepayment
                    ->where('fee_structure_id', $fs->id)
                    ->where('student_id', $student->id)
                    ->first();

                return [
                    'paymentinfo' => $payment,
                    'fee_structure_id' => $fs->id,
                    'quarter' => $fs->quarter_title,
                    'year' => $fs->std_current_year,
                    'total_amount' => $fs->feeHeads->sum('amount'),
                    'paid' => $payment ? true : false,
                    'paid_amount' => $payment->amount ?? 0,
                    'status' => $payment->status ?? 'NOT PAID',
                ];
            });

            return [
                'studentinfo' => [
                    'id' => $student->id,
                    'fullname' => $student->fullname,
                    'rollno' => $student->roll_no,
                    'dob' => $student->dob,
                    'gender' => $student->gender == 1 ? 'male' : 'female',
                    'mobile' => $student->mobile_no,
                    'email' => $student->mail_id
                ],
                'batch' => $student->batchmaster->batch_name ?? '',
                'programgroup' => $student->programgroup->program_code ?? '',
                'programinfo' => $student->programgroup->programInfo->name ?? '',
                'current_year' => $student->current_year,
                'fee_status' => $fsWithStatus
            ];
        });

        // ---- Return view ----
        return view('admin.accounts.fee-payment-records', [
            'data' => $students
        ]);
    }


    public function manualFeePayment(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'fee_structure_id' => 'required',
            'amount' => 'required|numeric',
            'transaction_id' => 'required',
            'transaction_date' => 'required|date',
            'gateway_type_id' => 'required'
        ]);

        //generate Invoice #
        $invoice = StaticController::generateInvoiceId();

        $rec = new StudentPayment();
        $rec->invoice_id = $invoice;
        $rec->student_id = $request->student_id;
        $rec->fee_structure_id = $request->fee_structure_id;
        $rec->status = 'success';
        $rec->amount = $request->amount;
        $rec->transaction_id = $request->transaction_id;
        $rec->transaction_date = $request->transaction_date;
        $rec->gateway_type_id = $request->gateway_type_id;
        $rec->message = 'Manual payment entry fom office';
        $rec->save();
        return redirect()->back()->with('success', 'Payment updated successfully!');
    }

    public function generateInvoice($rollno)
    {


        $student = StudentMaster::with([
            'campusmaster',
            'batchmaster',
            'programGroup.feeprogpivot.feeStructure',
            'programGroup.programInfo',
            'feepayment' // your payment table
        ])->where('roll_no', $rollno)->firstOrFail();

        $paidInvoices = [];
        $totalPaid = 0;

        foreach ($student->programGroup->feeprogpivot as $pivot) {

            foreach ($pivot->feeStructure as $fee) {

                // check if payment done
                $payment = $student->feepayment
                    ->where('fee_structure_id', $fee->id)
                    ->where('status', 'success')
                    ->first();

                if ($payment) {
                    $amount = FeeStructureHasHead::where('fee_structure_id', $fee->id)->sum('amount');

                    $paidInvoices[] = [
                        'quarter' => $fee->quarter_title,
                        'payable_amount' => $amount,
                        'status' => 'PAID',
                        'paid_on' => $payment->transaction_date ?? 'N/A',
                        'txn_id' => $payment->transaction_id ?? 'N/A',
                        'inv_id' => $payment->invoice_id ?? 'N/A',

                    ];

                    $totalPaid += $amount;
                }
            }
        }

        return view('pdf.fee-invoice', [
            'student' => $student,
            'paidInvoices' => $paidInvoices,
            'total_paid' => $totalPaid,
            'invoice_no' => "INV-" . now()->format('Ymd') . "-" . $student->id,
        ]);
    }


    function generateFeeReciept($studentId, $feeId)
    {
        $student = StudentMaster::with([
            'campusmaster',
            'batchmaster',
            'programGroup.feeprogpivot.feeStructure',
            'feepayment',
            'programGroup.programInfo'

        ])->findOrFail($studentId);

        // Find the selected fee structure
        $fee = $student->programGroup
            ->feeprogpivot
            ->pluck('feeStructure')
            ->flatten()
            ->where('id', $feeId)
            ->first();

        if (!$fee) {
            abort(404, "Fee structure not found");
        }

        // Find the payment for this fee
        $payment = $student->feepayment
            ->where('fee_structure_id', $feeId)
            ->where('status', 'success')
            ->first();

        if (!$payment) {
            abort(404, "No successful payment found for this fee");
        }

        // Amount calculation
        $amount = FeeStructureHasHead::where('fee_structure_id', $feeId)
            ->sum('amount');

        $invoiceData = [
            'student' => $student,
            'fee' => $fee,
            'payment' => $payment,
            'amount' => $amount,
            'invoice_no' => "INV-" . now()->format('Ymd') . "-" . $student->id . "-" . $feeId,
        ];

        return view('pdf.fee-reciept', $invoiceData);
    }

    function studentValidation()
    {
        return view('student.fee-payment');
    }

    public function studentFeeStatus(Request $request)
    {
        $request->validate([
            'rollno' => 'required'
        ]);
        $roll = trim($request->rollno);


        // Fetch student
        $student = StudentMaster::with([
            'batchmaster',
            'programgroup.programInfo',
            'stdfeestructure',
            'stdfeestructure.programspivot',
            'feepayment'
        ])->where('roll_no', $roll)
            ->first();



        // ---- FETCH APPLICABLE FEE STRUCTURES ----
        $applicableFS = FeesStructure::where('batch_id', $student->batch)
            ->whereHas('programspivot', function ($q) use ($student) {
                $q->where('std_program_id', $student->programme);
            })
            ->whereIn('std_current_year', range(1, $student->current_year))
            ->where('is_payable', 1)
            ->get();

        $feeStatus = $applicableFS->map(function ($fs) use ($student) {

            $payment = $student->feepayment
                ->where('fee_structure_id', $fs->id)
                ->where('student_id', $student->id)
                ->where('status', 'initiate')
                ->first();

            return [
                'fee_structure_id' => $fs->id,
                'fee_structure_name' => $fs->quarter_title,
                'year' => $fs->std_current_year,
                'total_amount' => $fs->feeHeads->sum('amount'),
                'paid' => $payment ? true : false,
                'paid_amount' => $payment->amount ?? 0,
                'status' => $payment->status ?? 'NOT PAID',
                'paymentinfo' => $payment
            ];
        })->filter(fn($item) => $item['paid'] === false) // only unpaid
            ->values();;

        // ---- FINAL RESPONSE ARRAY ----
        $studentData = [
            'studentinfo' => [
                'id'       => $student->id,
                'fullname' => $student->fullname,
                'rollno'   => $student->roll_no,
                'mobile'   => $student->mobile_no,
                'email'    => $student->mail_id,
            ],
            'programinfo' => $student->programgroup->programInfo->name ?? '',
            'batch'       => $student->batchmaster->batch_name ?? '',
            'current_year' => $student->current_year,
            'feesinfo'    => $feeStatus
        ];

        return view('student.gateway-selection', [
            'data' => $studentData
        ]);
    }

    function createOrder(Request $request)
    {
        $studentId = $request->studentId;
        $fee_structure_id = $request->fee_structure_id;
        $gateway = $request->gateway;

        $payMaster = PaymentGatewayType::where('title', $gateway)->first();
        $payMasterId = $payMaster->id;

        //generate Invoice #
        $invoice = $studentId . Carbon::now()->timestamp;

        for ($i = 0; $i < count($fee_structure_id); $i++) {

            //find fee Structure Amount
            $amount = FeeStructureHasHead::where('fee_structure_id', $fee_structure_id[$i])->sum('amount');

            $rec = new StudentPayment();
            $rec->invoice_id = $invoice;
            $rec->student_id = $studentId;
            $rec->fee_structure_id = $fee_structure_id[$i];
            $rec->status = 'intiated';
            $rec->amount = $amount;
            $rec->transaction_date = Carbon::now();
            $rec->gateway_type_id = $payMasterId;
            $rec->save();
        }

        //find total amount
        $payableAmount =   StudentPayment::where('invoice_id', $invoice)->where('student_id', $studentId)->sum('amount');

        if ($gateway == 'easebuzz') {
            $this->startEasebuzzPayment($studentId, $payableAmount, $invoice);
        }

        if ($gateway == 'billdesk') {
            $this->startBilldeskPayment($studentId, $payableAmount, $invoice);
        }
    }

    //Easebuzz integration
    public function startEasebuzzPayment($studentId, $payableAmount, $invoice)
    {

        $student = StudentMaster::find($studentId);
        $client = new \GuzzleHttp\Client();
        $key = env('EASEBUZZ_KEY_TEST');
        $txnid = $invoice;
        $userid = $studentId;
        $name = $student->fullname;
        $amount = $payableAmount;
        $phone = $student->mobile_no;
        $email = $student->mail_id;
        $productinfo = 'Salesian College Autonomous - Fee Payment';
        $salt = env('EASEBUZZ_SALT_TEST');
        $hash = "$key|$txnid|$amount|$productinfo|$name|$email|$userid||||||||||$salt";

        $hashSequence = strtolower(hash("sha512", $hash));

        $intiateLink = env('EASEBUZZ_INITIATE_URL_TEST');

        $response = $client->request('POST', $intiateLink, [
            'form_params' => [
                'key' => $key,
                'txnid' => $txnid,
                'amount' => $amount,
                'productinfo' => $productinfo,
                'firstname' => $name,
                'phone' => $phone,
                'email' => $email,
                'surl' => route('payment.success'),
                'furl' => route('payment.failure'),
                'hash' => $hashSequence,
                'udf1' => $userid,
                'udf2' => '',
                'udf3' => '',
                'udf4' => '',
                'udf5' => '',
                'udf6' => '',
                'udf7' => '',
                'address1' => '',
                'address2' => '',
                'city' => '',
                'state' => '',
                'country' => '',
                'zipcode' => '',
                'show_payment_mode' => '',
            ],
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);

        $body = $response->getBody()->getContents();
        $apiResponse = json_decode($body, true);

        if ($apiResponse['status'] == 1) {
            // Redirect to payment page
            $accessKey = $apiResponse['data'];

            return redirect(env('EASEBUZZ_PAYMENT_URL_TEST') . $accessKey);
        } else {
            return response()->json($apiResponse);
        }
    }

    public function paymentSuccess(Request $request)
    {

        $hash  =  $request->hash;
        $amount = $request->amount;
        $msg = $request->error_Message;
        $easepayid = $request->easepayid;
        $status = $request->status;
        $txnid = $request->txnid;
        $userId = $request->udf1;


        //Online Transaction - Update Payment Record
        StudentPayment::where('invoice_id', $txnid)
            ->update(
                [
                    'payment_gateway_id' => $easepayid,
                    'captured_amount' => $amount,
                    'status' => $status,
                    'message' => $msg,
                    'hash' => $hash,

                ]
            );

        $studentInfo = StudentMaster::find($userId);
        return view('includes.success-page', [
            'studentinfo' => $studentInfo,
            'txnid' => $txnid,
            'amount' => $amount,
            'status' => $status,
            'gateway_id' => $easepayid
        ]);
    }

    public function paymentFailure(Request $request)
    {


        $hash  =  $request->hash;
        $amount = $request->amount;
        $msg = $request->error_Message;
        $easepayid = $request->easepayid;
        $status = $request->status;
        $txnid = $request->txnid;
        $userId = $request->udf1;

        //Record Transaction in Payments Log
        $log = new FailedTransactionLog();
        $log->txnid = $txnid;
        $log->gateway_id = $easepayid;
        $log->user_id = $userId;
        $log->amount = $amount;
        $log->hash = $hash;
        $log->msg = $msg;
        $log->status = $status;
        $log->save();
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

        $payment = StudentPayment::where('invoice_id', $txnid)->get();

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



    //BillDesk Integeration
    private function startBilldeskPayment($student, $amount, $txnid)
    {
        $payload = [
            "mercid" => "XXXXX",
            "orderid" => $txnid,
            "amount" => $amount,
            "order_date" => date('Y-m-d H:i:s'),
            "currency" => "INR",
            "ru" => url('payment/billdesk-response'),
        ];

        // Build checksum, send to Billdesk (pseudo)
        $checksum = hash('sha256', json_encode($payload));

        return view('payment.billdesk-redirect', [
            'payload' => $payload,
            'checksum' => $checksum
        ]);
    }
}
