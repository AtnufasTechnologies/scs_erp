<?php

namespace App\Helpers;

use App\Models\AdmissionFinalPhase;
use App\Models\AdmissionFirstPhase;
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

    if ($campusId == null) {
      $programs = StudentProgram::with('applicationCount')

        ->whereHas('applicationmaster', function ($query) {
          $query->where('payment_gateway_status', 'success'); //approved applications only
          $query->whereHas('registrationmaster', function ($query) {
            $query->where('application_type', 'UG');
          });
        })->distinct()
        ->get(); // Return an empty collection if no campus is set
    } else {
      $programs = StudentProgram::with('applicationCount')
        ->whereHas('campusmaster', function ($query) use ($campusId) {
          $query->where('id', $campusId);
        })
        ->whereHas('applicationmaster', function ($query) {
          $query->where('payment_gateway_status', 'success'); //approved applications only
          $query->whereHas('registrationmaster', function ($query) {
            $query->where('application_type', 'UG');
          });
        })->distinct()
        ->get();
    }

    return $programs;
  }

  static function returnToDashboard()
  {
    return redirect()->route('admin.dashboard')->with('error', 'You do not have permission to access this page.');
  }

  static function moveToAdmissonFinalPhase($id)
  {
    $data = AdmissionFirstPhase::find($id);

    $application_id = $data->application_id;
    $reg_id = $data->reg_id;
    AdmissionFinalPhase::create([
      'application_id' => $application_id,
      'reg_id' => $reg_id,
    ]);
  }

  static function fetchPhase1FinalStatus($reg_id)
  {
    $status = AdmissionFirstPhase::where('reg_id', $reg_id)->value('final_status');
    return $status;
  }


  //PG

  static function getPgProgramGroups()
  {
    $campusId = self::getCampusSettings();

    if ($campusId == null) {
      $programs = StudentProgram::with('applicationCount')

        ->whereHas('applicationmaster', function ($query) {
          $query->where('payment_gateway_status', 'success'); //approved applications only
          $query->whereHas('registrationmaster', function ($query) {
            $query->where('application_type', 'PG');
          });
        })->distinct()
        ->get(); // Return an empty collection if no campus is set
    } else {
      $programs = StudentProgram::with('applicationCount')
        ->whereHas('campusmaster', function ($query) use ($campusId) {
          $query->where('id', $campusId);
        })
        ->whereHas('applicationmaster', function ($query) {
          $query->where('payment_gateway_status', 'success'); //approved applications only
          $query->whereHas('registrationmaster', function ($query) {
            $query->where('application_type', 'PG');
          });
        })->distinct()
        ->get();
    }

    return $programs;
  }
}
