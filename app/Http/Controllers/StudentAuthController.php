<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetOtpMail;
use App\Models\PasswordReset;
use App\Models\StudentMaster;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class StudentAuthController extends Controller
{
  /**
   * Display student login form
   */
  public function index()
  {
    return view('auth.student-login');
  }

  /**
   * Handle student login using roll number
   */
  public function login(Request $request)
  {
    $request->validate([
      'roll_no' => 'required|string',
      'password' => 'required|string',
    ]);

    // Find user account associated with this student
    $user = User::where('roll_no', strtolower($request->roll_no))
      ->where('status', 'ACTIVE')
      ->first();

    if (!$user) {
      return redirect()->back()->with('error', 'No login account found. Please contact administrator.');
    }

    // Verify password
    if (Hash::check($request->password, $user->password)) {

      Auth::login($user, true);
      return redirect()->route('student.console.dashboard')->with('success', 'Login Successful');
    } else {
      return redirect()->back()->with('error', 'Incorrect Password');
    }
  }

  /**
   * Display forgot password form for students
   */
  public function forgotPassword()
  {
    return view('auth.student-forgot-password');
  }

  /**
   * Send password reset link to student email
   */
  public function sendPasswordReset(Request $request)
  {
    $request->validate([
      'roll_no' => 'required|string',
      'email' => 'required|email',
    ]);

    // Find student by roll number
    $student = StudentMaster::where('roll_no', $request->roll_no)
      ->where('mail_id', $request->email)
      ->first();

    if (!$student) {
      return redirect()->back()->with('error', 'Roll Number and Email do not match our records');
    }

    // Find associated user account
    $user = User::where('student_id', $student->id)->first();

    if (!$user) {
      return redirect()->back()->with('error', 'No login account found. Please contact administrator.');
    }

    // Generate reset token
    $code = sha1(uniqid());

    $rec = new PasswordReset();
    $rec->email = $user->email;
    $rec->token = $code;
    $rec->status = 1;
    $rec->save();

    $details = [
      'token' => $code,
    ];

    Mail::to($student->mail_id)->send(new PasswordResetOtpMail($details));

    return redirect()->back()->with('success', 'Password reset link has been sent to your email');
  }

  /**
   * Verify reset token and show update password form
   */
  public function verifyResetToken($code)
  {
    $data = PasswordReset::where('token', $code)
      ->where('status', 1)
      ->first();

    if ($data) {
      return view('auth.student-update-password', ['data' => $data]);
    } else {
      return redirect()->route('student.login')->with('error', 'Link Expired. Please request a new reset link.');
    }
  }

  /**
   * Update student password
   */
  public function updatePassword(Request $request)
  {
    $request->validate([
      'password' => 'required|string|min:6|max:190',
      'confirm_password' => 'required|same:password',
    ]);

    $user = User::where('email', $request->email)->first();

    if ($user) {
      $user->password = Hash::make($request->password);
      $user->decrypted_password = $request->password;
      $user->save();

      // Invalidate the token
      PasswordReset::where('email', $request->email)->update(['status' => 0]);

      return redirect()->route('student.login')->with('success', 'Password updated successfully. Please login with your new password.');
    } else {
      return redirect()->route('student.login')->with('error', 'User not found');
    }
  }

  /**
   * Logout student
   */
  public function logout()
  {
    Auth::logout();
    return redirect()->route('student.login')->with('success', 'Logged out successfully');
  }
}
