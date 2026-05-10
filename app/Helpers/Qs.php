<?php

namespace App\Helpers;

use App\Models\AdmissionFinalPhase;
use App\Models\AdmissionFirstPhase;
use App\Models\BatchMaster;
use App\Models\StudentProgram;
use App\Models\SubjectHasStudentProgam;
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

  /**
   * Convert number to words (Indian Rupees format)
   * 
   * @param float $number
   * @return string
   */
  static function numberToWords($number)
  {
    $number = (int)$number;

    if ($number == 0) {
      return 'Zero Rupees';
    }

    $ones = [
      '',
      'One',
      'Two',
      'Three',
      'Four',
      'Five',
      'Six',
      'Seven',
      'Eight',
      'Nine',
      'Ten',
      'Eleven',
      'Twelve',
      'Thirteen',
      'Fourteen',
      'Fifteen',
      'Sixteen',
      'Seventeen',
      'Eighteen',
      'Nineteen'
    ];

    $tens = [
      '',
      '',
      'Twenty',
      'Thirty',
      'Forty',
      'Fifty',
      'Sixty',
      'Seventy',
      'Eighty',
      'Ninety'
    ];

    $scales = [
      '',
      'Thousand',
      'Lakh',
      'Crore'
    ];

    $words = [];

    // Handle crores
    if ($number >= 10000000) {
      $crores = (int)($number / 10000000);
      $words[] = self::convertGroup($crores, $ones, $tens) . ' Crore';
      $number %= 10000000;
    }

    // Handle lakhs
    if ($number >= 100000) {
      $lakhs = (int)($number / 100000);
      $words[] = self::convertGroup($lakhs, $ones, $tens) . ' Lakh';
      $number %= 100000;
    }

    // Handle thousands
    if ($number >= 1000) {
      $thousands = (int)($number / 1000);
      $words[] = self::convertGroup($thousands, $ones, $tens) . ' Thousand';
      $number %= 1000;
    }

    // Handle hundreds
    if ($number >= 100) {
      $hundreds = (int)($number / 100);
      $words[] = $ones[$hundreds] . ' Hundred';
      $number %= 100;
    }

    // Handle remaining (tens and ones)
    if ($number > 0) {
      $words[] = self::convertGroup($number, $ones, $tens);
    }

    return implode(' ', $words) . ' Rupees';
  }

  /**
   * Convert a group of numbers (up to 99) to words
   * 
   * @param int $number
   * @param array $ones
   * @param array $tens
   * @return string
   */
  private static function convertGroup($number, $ones, $tens)
  {
    if ($number < 20) {
      return $ones[$number];
    }

    $tensDigit = (int)($number / 10);
    $onesDigit = $number % 10;

    return trim($tens[$tensDigit] . ' ' . $ones[$onesDigit]);
  }


  static function getAvailableCourseSeats($campusId)
  {
    $batchnfo = BatchMaster::where('admission_active_batch', 1)->first();
    $batch_id = $batchnfo->id;
    $data = SubjectHasStudentProgam::where('campus_id', $campusId)
      ->where('batch_id', $batch_id)
      // ->where('total_seats', '!=', null)
      // ->where('total_available_seats', '!=', 0)
      ->with('studentprograminfo')
      ->get();
    return $data;
  }
}
