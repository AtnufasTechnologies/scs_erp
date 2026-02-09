<?php

namespace App\Http\Controllers;

use App\Models\SubjectHasDeptAdmin;
use App\Models\UserCampusSetting;
use Illuminate\Http\Request;

class TestController extends Controller
{
    function DeptCampusMapping()
    {

        //get the campus_id of each dept user inside

        $data = SubjectHasDeptAdmin::with('subject')->get();

        foreach ($data as $item) {

            $userId = $item->user_id;
            $campusId = $item->subject->campus_id;
            $created = 0;
            $check = UserCampusSetting::where('user_id', $userId)->where('campus_id', $campusId)->doesntExist();
            if ($check) {
                $new = new UserCampusSetting();
                $new->user_id = $userId;
                $new->campus_id = $campusId;
                $new->save();
                $created++;
            }
        }
        return 'Created ' . $created . ' records';
    }
}
