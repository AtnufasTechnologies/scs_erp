<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\WorkDiary;
use App\Models\MethodologyMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Work Diary Analysis Controller
 * 
 * This controller provides various analysis methods for work diary entries
 * using the methodology_masters table for better reporting and insights.
 */
class WorkDiaryAnalysisController extends Controller
{
  /**
   * Get methodology usage statistics for a faculty member
   * 
   * Example usage:
   * - Total entries per methodology
   * - Most frequently used teaching methods
   * - Methodology distribution over time
   */
  public function getMethodologyStatistics(Request $request)
  {
    $facultyId = $request->input('faculty_id');
    $startDate = $request->input('start_date', Carbon::now()->subMonth());
    $endDate = $request->input('end_date', Carbon::now());

    $statistics = WorkDiary::select('methodology', DB::raw('count(*) as total'))
      ->when($facultyId, function ($query) use ($facultyId) {
        return $query->where('faculty_id', $facultyId);
      })
      ->whereBetween('date', [$startDate, $endDate])
      ->whereNotNull('methodology')
      ->groupBy('methodology')
      ->orderBy('total', 'desc')
      ->get();

    // Join with methodology_masters to get descriptions
    $statistics = $statistics->map(function ($stat) {
      $methodology = MethodologyMaster::where('name', $stat->methodology)->first();
      return [
        'methodology' => $stat->methodology,
        'description' => $methodology->description ?? '',
        'total_entries' => $stat->total,
        'percentage' => 0 // Calculate percentage
      ];
    });

    $totalEntries = $statistics->sum('total_entries');
    $statistics = $statistics->map(function ($stat) use ($totalEntries) {
      $stat['percentage'] = $totalEntries > 0 ? round(($stat['total_entries'] / $totalEntries) * 100, 2) : 0;
      return $stat;
    });

    return response()->json([
      'success' => true,
      'data' => $statistics,
      'total_entries' => $totalEntries
    ]);
  }

  /**
   * Get class type distribution
   * 
   * Analyze distribution of regular, extra, and substitution classes
   */
  public function getClassTypeDistribution(Request $request)
  {
    $facultyId = $request->input('faculty_id');
    $startDate = $request->input('start_date', Carbon::now()->subMonth());
    $endDate = $request->input('end_date', Carbon::now());

    $distribution = WorkDiary::select('class_type', DB::raw('count(*) as total'))
      ->when($facultyId, function ($query) use ($facultyId) {
        return $query->where('faculty_id', $facultyId);
      })
      ->whereBetween('date', [$startDate, $endDate])
      ->whereNotNull('class_type')
      ->groupBy('class_type')
      ->get();

    return response()->json([
      'success' => true,
      'data' => $distribution
    ]);
  }

  /**
   * Get weekly teaching method diversity
   * 
   * Shows how many different methodologies were used each week
   * Higher diversity indicates varied teaching approaches
   */
  public function getWeeklyMethodologyDiversity(Request $request)
  {
    $facultyId = $request->input('faculty_id');
    $weeks = $request->input('weeks', 4);

    $data = WorkDiary::select(
      DB::raw('YEARWEEK(date) as year_week'),
      DB::raw('COUNT(DISTINCT methodology) as methodology_count'),
      DB::raw('COUNT(*) as total_entries')
    )
      ->when($facultyId, function ($query) use ($facultyId) {
        return $query->where('faculty_id', $facultyId);
      })
      ->where('date', '>=', Carbon::now()->subWeeks($weeks))
      ->whereNotNull('methodology')
      ->groupBy('year_week')
      ->orderBy('year_week', 'desc')
      ->get();

    return response()->json([
      'success' => true,
      'data' => $data
    ]);
  }

  /**
   * Compare methodology usage across faculty members
   * 
   * Useful for administrative analysis and best practice sharing
   */
  public function compareMethodologyAcrossFaculty(Request $request)
  {
    $startDate = $request->input('start_date', Carbon::now()->subMonth());
    $endDate = $request->input('end_date', Carbon::now());

    $comparison = WorkDiary::select(
      'faculty_id',
      'methodology',
      DB::raw('COUNT(*) as usage_count')
    )
      ->whereBetween('date', [$startDate, $endDate])
      ->whereNotNull('methodology')
      ->groupBy('faculty_id', 'methodology')
      ->with('faculty:id,name')
      ->get()
      ->groupBy('faculty_id');

    return response()->json([
      'success' => true,
      'data' => $comparison
    ]);
  }
}
