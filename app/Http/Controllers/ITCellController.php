<?php

namespace App\Http\Controllers;

use App\Models\AdmissionApplication;
use Illuminate\Http\Request;

class ITCellController extends Controller
{

    function verifyPayment(int $id)
    {
        $applicationRecord = AdmissionApplication::find($id);
        if (!$applicationRecord) {
            return back()->with('error', 'Application not found.');
        }
        $txnid = $applicationRecord->application_code;
        // Call the payment gateway API to verify payment status
        // This is a placeholder. You need to implement actual API call and response handling based on your payment gateway's documentation.

        $response = StaticController::easebuzz_verifyPaymentWithHash($txnid);
        if ($response['status'] == false) {
            return back()->with('error', $response['msg']);
        }
        $data =  $response['msg']['0'];
        return view('admin.itcell.ez-payment-verification', ['data' => $data]);
    }

    function updateApplicationPayment(Request $request)
    {
        $request->validate([
            'application_code' => 'required|string',
        ]);
        $id = $request->id;
        $applicationRecord = AdmissionApplication::find($id);
        if (!$applicationRecord) {
            return back()->with('error', 'Application not found.');
        }

        // Update the payment status in the database
        $txnid = $request->application_code;
        $applicationRecord->application_code = $txnid;
        $applicationRecord->save();

        //verify payment again to update the status
        $txnid = $request->application_code;
        $response = StaticController::easebuzz_verifyPaymentWithHash($txnid);
        if ($response['status'] == false) {
            return back()->with('error', $response['msg']);
        }
        $data =  $response['msg']['0'];
        if ($data['status'] == 'success') {
            $applicationRecord->update([
                'payment_gateway_ref' => $data['easepayid'],
                'captured_amount' => $data['amount'],
                'hash' => $data['hash'],
                'payment_gateway_status' => $data['status'],
                'msg' => $data['error_Message'],
            ]);
        }

        return back()->with('success', 'Payment status updated successfully.');
    }
}
