<?php

namespace App\Helpers;

use App\Models\StudentProgram;
use App\Models\UserCampusSetting;
use Illuminate\Support\Facades\Auth;

class Qs
{
  static function getCampusSettings()
  {
    $campus_id = UserCampusSetting::where('user_id', Auth::id())->value('campus_id');
    return $campus_id;
  }

  static function getProgramGroups()
  {
    $campusId = self::getCampusSettings();

    $programs = StudentProgram::with('applicationCount')

      ->whereHas('campusmaster', function ($query) use ($campusId) {
        $query->where('id', $campusId);
      })
      ->whereHas('applicationmaster', function ($query) {
        $query->where('application_status', 1); //approved applications only
        $query->whereHas('registrationmaster.programinfo', function ($query) {
          $query->where('name', 'UG');
        });
      })->distinct()
      ->get();

    return $programs;
  }

  static function returnToDashboard()
  {
    return redirect()->route('admin.dashboard')->with('error', 'You do not have permission to access this page.');
  }
}
