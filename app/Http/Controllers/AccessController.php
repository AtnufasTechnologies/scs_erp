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

        $campusId =  StaticController::fetchCampusSettings();
        $departments = Subject::whereHas('programs', function () use ($campusId) {
            if ($campusId != null) {
                $this->where('campus_id', $campusId);
            }
        })->get();

        $data = User::whereHas('userroletype', function ($q) {
            //Head of Department Admin Role
        })->get();
        return view('admin.user-manager.dept-access', ['departments' => $departments, 'data' => $data]);
    }


    function assignDeptAccess(Request $request)
    {

        $request->validate([
            'department' => 'required',
            'password' => 'required|min:6',
        ]);
        $data = Subject::where('id', $request->department)->first();

        $departmentId = $data->id;
        //create user
        $rec = new User();
        $rec->name =  $data->title . ' Dept Admin';
        $rec->email = $data->title . '@salesiancollege.net';
        $rec->password = Hash::make($request->password);
        $rec->otp_verification = 1;
        $rec->status = 'ACTIVE';
        $rec->save();


        //assign campus access permission
        UserHasRole::create([
            'user_id' => $rec->id,
            'role_name' =>  $request->role_type, //Department Admin
        ]);

        //assign department
        SubjectHasDeptAdmin::create([
            'subject_id' => $departmentId,
            'user_id' => $rec->id,
            'campus_id' => $request->campus,
        ]);


        return back()->with('success', 'Departmental Access Created.');
    }
}
