<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\AdminNotify;
use App\Models\AdmissionApplication;
use App\Models\AnnualSession;
use App\Models\CourseCombination;
use App\Models\Department;
use App\Models\FeeHead;
use App\Models\FeeStructureHasHead;
use App\Models\ProgramGroup;
use App\Models\StudentPayment;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class StaticController extends Controller
{

  static function wait_page_process_cashfree($response)
  {
    $order_id = $response->order_id;
    $captured_amount = $response->order_amount;


    //Online Transaction - Update User Registration Status
    AdmissionApplication::where('application_id', $order_id)
      ->update(
        [
          'payment_gateway_id' => $response->cf_order_id,
          'captured_amount' => $captured_amount,
          'payment_gateway_status' => $response->order_status,
          'captured_currency' => $response->order_currency,

        ]
      );

    //ReLogin user
    $userId = $response->customer_details->customer_id;
    $userdata = User::find($userId);
    Auth::login($userdata, true);
  }


  static function payment_failed_cashfree($response)
  {
    $order_id = $response->order_id;

    $captured_amount = $response->order_amount;

    //Online Transaction
    AdmissionApplication::where('application_id', $order_id)
      ->update(
        [
          'payment_gateway_id' => $response->cf_order_id,
          'captured_amount' => $captured_amount,
          'payment_gateway_status' => $response->order_status,
          'captured_currency' => $response->order_currency,

        ]
      );

    //ReLogin user
    $userId = $response->customer_details->customer_id;
    $userdata = User::find($userId);
    Auth::login($userdata, true);
  }



  // static function myAdminNotification($message, $message_type)
  // {
  //   $rec = new AdminNotify();
  //   $rec->message = $message;
  //   $rec->message_type = $message_type;
  //   $rec->status = 'UNREAD';
  //   $rec->save();
  // }

  //S3 
  static function s3_resize_image_uploader($img, $path, $resizeto)
  {

    $newImageName = $img->getClientOriginalName();
    //add timestamp to stop duplication
    $filename =  Carbon::now()->timestamp . '_' . $newImageName;
    $filename = preg_replace('/\s+/', '', $filename);
    $image_resize =  Image::make($img->getRealPath());

    $image_resize->widen($resizeto, function ($constraint) {
      $constraint->upsize();
    });

    $image_resize = $image_resize->stream();
    $path = $path . '/' . $filename;
    Storage::disk('s3')->put($path, $image_resize->__toString());

    return $filename;
  }

  static function s3_file_uploader($file, $path)
  {

    $newImageName = $file->getClientOriginalName();
    //add timestamp to stop duplication
    $filename =  Carbon::now()->timestamp . '_' . $newImageName;
    $filename = preg_replace('/\s+/', '', $filename);

    $path = $path . '/' . $filename;
    Storage::disk('s3')->put($path, file_get_contents($file));

    return $filename;
  }

  static function easebuzz_verifyPaymentWithHash($txnid)
  {
    $key = env('EASEBUZZ_KEY');
    $salt = env('EASEBUZZ_SALT');

    $hash_string = "$key|$txnid|$salt";
    $hash = hash("sha512", $hash_string);

    $response = Http::withHeaders([
      'Content-Type' => 'application/json'
    ])->post('https://dashboard.easebuzz.in/transaction/v2.1/retrieve', [

      'txnid' => $txnid,
      'key' => $key,
      'hash' => $hash,

    ]);
    return $response->json();
  }

  static function sendOtp_onMail($user, $otpcode)
  {


    $html = View::make('emails.otp-mail', ['otpcode' => $otpcode])->render();

    $response = Http::withToken(env('RESEND_API_KEY'))
      ->post('https://api.resend.com/emails', [
        'from' => 'salesian college autonomous <onboarding@resend.dev>', // Use verified sender
        'to' =>  $user,
        'subject' => 'Salesian College Autonomous - Otp Verification Code',
        'html' => $html,
      ]);

    return $response->json();
  }

  // static function fetchDepartment($campus, $applicationType)
  // {
  //   return  Department::where('campus_id', $campus)->where('main_program_id', $applicationType)->get();
  // }

  static function activeSessionId()
  {
    $rec =  AnnualSession::where('status', 1)->first();
    $sessionId = $rec->id;
    return $sessionId;
  }

  static function feeStructureTotal($id)
  {
    $total =  FeeStructureHasHead::where('fee_structure_id', $id)->sum('amount');
    return $total;
  }

  static function fetchProgramGroup($campusid)
  {
    $data = ProgramGroup::where('campus_id', $campusid)->with('programInfo')->get();
    return $data;
  }

  static function generateInvoiceId()
  {
    $year = now()->format('Y');
    $count = StudentPayment::whereYear('created_at', $year)->count() + 1;
    return "INV{$year}-" . str_pad($count, 5, '0', STR_PAD_LEFT);
  }
}
