<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetOtpMail;
use App\Models\Faculty;
use App\Models\PasswordReset;
use App\Models\User;
use App\Models\UserHasRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Laravel\Sanctum\PersonalAccessToken;

class LoginController extends Controller
{
    function index()
    {
        return view('auth.login');
    }

    function login(Request $request)
    {

        $user = User::where('email', $request->email)->where('status', 'ACTIVE')->first();

        if ($user) {

            if (Hash::check($request->password, $user->password)) {
                Auth::login($user, true);
                return redirect('erp/admin/dashboard')->with('success', 'Login Success');
            } else {
                return redirect('/')->with('error', 'Password Incorrect');
            }
        } else {
            return redirect('/')->with('error', 'User Not Found');
        }
    }

    function userAuth($id)
    {
        $user = User::find($id);
        if ($user) {
            if ($user->status == 'ACTIVE') {
                return response()->json([
                    'status' => true,
                    'user' => $user
                ]);
            }
            return response()->json(['message' => 'Signing Out', 'signoutaction' => true], 401);
        } else {
            return response()->json(['signoutaction' => true, 'message' => 'User Not Found...Siging Out'], 404);
        }
    }

    function addfacultyAccess(Request $request)
    {

        $validate = $request->validate([
            'faculty_id' => 'required',
            'password' => 'required|string|min:6|max:190',
        ]);

        $facultyRecord = Faculty::find($request->faculty_id);

        $fullname = $facultyRecord->fname . ' ' .  $facultyRecord->lname;
        $rec = new User();
        $rec->name = $fullname;
        $rec->email = $facultyRecord->email;
        $rec->phone = $facultyRecord->phone;
        $rec->decrypted_password = $request->password;
        $rec->password = Hash::make($request->password);
        $rec->otp_verification = 1;
        $rec->status = 'ACTIVE';
        $rec->save();

        $user = $rec->id;

        //Add user role
        $role = new UserHasRole();
        $role->user_id = $user;
        $role->role_type = 'teacher';
        $role->save();

        return response()->json(['message' => 'Faculty Access Created for ' . $fullname]);
    }

    function erpLogin(Request $request)
    {

        $user = User::where('email', $request->email)->where('status', 'ACTIVE')->first();

        if ($user) {

            if (Hash::check($request->password, $user->password)) {

                //checking user role
                $userRole = UserHasRole::where('user_id', $user->id)->first();

                if ($userRole->role_type == 'admin') {
                    $data['user'] = $user;
                    $data['user_role'] = $userRole->role_type;
                    $data['campus_auth'] = $userRole->campus; //1 = Sonada,2 = Siliguri

                } else if ($userRole->role_type == 'teacher') {

                    $data['user'] = $user;
                    $data['user_role'] = $userRole->role_type;
                } else if ($userRole->role_type == 'office') {

                    $data['user'] = $user;
                    $data['user_role'] = $userRole->role_type;
                } else {
                    //applicatant --- future student
                    $data['user'] = $user;
                    $data['user_role'] = $userRole->role_type;
                }


                return response()->json([
                    'status' => true,
                    'message' => 'Login Successful',
                    'data' => $data,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Password Incorrect',

                ], 401);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User Not Found',

            ], 404);
        }
    }

    function forgotPassword()
    {
        return view('auth.forgot-password');
    }

    function sendPasswordReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);
        $email =   $request->email;
        $userdata = User::where('email', $email)->first();

        if ($userdata != null) {
            $code = sha1(uniqid());
            $rec = new PasswordReset();
            $rec->email = $request->email;
            $rec->token = $code;
            $rec->status = 1;
            $rec->save();

            $details = [
                'token' =>  $code,
            ];


            Mail::to($email)->send(new PasswordResetOtpMail($details));

            return redirect()->back()->with('success', 'Reset Link Sent on Email');
        } else {
            return redirect()->back()->with('error', 'Email not found');
        }
    }

    function verifyResetToken($code)
    {
        $data =  PasswordReset::where('token', $code)->where('status', 1)->first();
        if ($data) {
            return view('auth.update-password', ['data' => $data]);
        } else {
            return redirect('login')->with('error', 'Link Expired... Please Reset Again');
        }
    }

    function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|max:190',
            'confirm_password' => 'required|same:password',
        ]);

        $data = User::where('email', $request->email)->first();
        if ($data) {
            $data->password = Hash::make($request->password);
            $data->save();

            //invalidate the token
            PasswordReset::where('email', $request->email)->update(['status' => 0]);

            return redirect()->route('login')->with('success', 'Password Updated Successfully. Please Login');
        } else {
            return redirect()->route('login')->with('info', 'User Not Found');
        }
    }
    function resetPassword()
    {
        return view('auth.reset-password');
    }


    function logout()
    {
        Auth::logout();
        return redirect('/')->with('success', 'Signed Out ');
    }
}
