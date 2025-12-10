<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use App\Models\User;
use App\Models\UserHasRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

                return redirect('erp/admin/dashboard')->with('success', 'Login Success');
                // return response()->json([
                //     'status' => true,
                //     'message' => 'Login Successful',
                //     'data' => $data,
                // ], 200);
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


    function logout()
    {
        Auth::logout();
        return redirect('/')->with('success', 'Signed Out ');
    }
}
