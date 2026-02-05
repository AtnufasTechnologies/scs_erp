<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\SubjectHasDeptAdmin;
use App\Models\User;
use App\Models\UserHasRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccessController extends Controller
{
    function deptAccess()
    {

        $departments = Subject::with('campusmaster:id,name')->get();
        $data = User::whereHas('userroletype', function ($q) {
            //Head of Department Admin Role
            $q->where('role_name', 'dept-admin-erp');
        })->with('subjectdeptadmin.subject')->get();
        return view('admin.user-manager.dept-access', ['departments' => $departments, 'data' => $data]);
    }


    function assignDeptAccess(Request $request)
    {

        $request->validate([
            'department' => 'required',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);
        $data = Subject::where('id', $request->department)->first();

        $departmentId = $data->id;
        //create user
        $rec = new User();
        $rec->name =  $request->name;
        $rec->email = $request->email;
        $rec->password = Hash::make($request->password);
        $rec->otp_verification = 1;
        $rec->status = 'ACTIVE';
        $rec->save();


        //assign campus access permission
        UserHasRole::create([
            'user_id' => $rec->id,
            'role_name' =>  'dept-admin-erp', //Department Admin
        ]);

        //assign department to user
        SubjectHasDeptAdmin::create([
            'subject_id' => $departmentId,
            'user_id' => $rec->id,
        ]);


        return back()->with('success', 'Departmental Access Created.');
    }

    function revokeDeptAccess($id)
    {
        $user = User::find($id);
        if ($user) {
            if ($user->status == 'ACTIVE') {


                User::where('id', $id)->update([
                    'status' => 'INACTIVE',
                ]);
            } else {
                User::where('id', $id)->update([
                    'status' => 'ACTIVE',
                ]);
            }
        }

        return back()->with('success', 'Done.');
    }

    function impersonateUser($id)
    {
        // Store the current admin's ID in session before impersonating
        session(['impersonate_admin_id' => auth()->id()]);

        // Log in as the target user
        $user = User::findOrFail($id);
        auth()->login($user);
        return redirect('erp/admin/dashboard')->with('success', 'You are now impersonating ' . $user->name);
    }
}
