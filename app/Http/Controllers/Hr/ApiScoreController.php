<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\ApiAcademicYear;
use App\Models\ApiFacultyScore;
use App\Models\ApiCategoryScore;
use App\Models\ApiPublication;
use App\Models\ApiActivity;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApiScoreController extends Controller
{
    /**
     * Display a listing of faculty API scores
     */
    public function index(Request $request)
    {
        $academicYears = ApiAcademicYear::orderBy('start_date', 'desc')->get();
        $selectedYear = $request->get('academic_year_id') ?? ApiAcademicYear::getActive()?->id;

        $query = ApiFacultyScore::with(['faculty', 'academicYear'])
            ->when($selectedYear, function ($q) use ($selectedYear) {
                $q->where('academic_year_id', $selectedYear);
            });

        if ($search = $request->get('search')) {
            $query->whereHas('faculty', function ($q) use ($search) {
                $q->where('FIRST_NAME', 'like', "%$search%")
                    ->orWhere('LAST_NAME', 'like', "%$search%")
                    ->orWhere('USER_CODE', 'like', "%$search%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $scores = $query->latest()->paginate(20);

        return view('hr.api_scores.index', compact('scores', 'academicYears', 'selectedYear', 'search', 'status'));
    }

    /**
     * Show the form for creating a new API score entry
     */
    public function create(Request $request)
    {
        $academicYears = ApiAcademicYear::orderBy('start_date', 'desc')->get();
        $faculties = Faculty::where('IS_LEFT', 0)->orderBy('FIRST_NAME')->get();
        $selectedFaculty = $request->get('faculty_id');
        $selectedYear = $request->get('academic_year_id') ?? ApiAcademicYear::getActive()?->id;

        return view('hr.api_scores.create', compact('academicYears', 'faculties', 'selectedFaculty', 'selectedYear'));
    }

    /**
     * Store a newly created API score
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'faculty_id' => 'required|exists:faculties,id',
            'academic_year_id' => 'required|exists:api_academic_years,id',
            'category_i_score' => 'nullable|numeric|min:0|max:10',
            'category_ii_score' => 'nullable|numeric|min:0|max:25',
            'category_iii_score' => 'nullable|numeric|min:0|max:10',
            'category_iv_score' => 'nullable|numeric|min:0|max:25',
            'category_v_score' => 'nullable|numeric|min:0|max:15',
            'category_vi_score' => 'nullable|numeric|min:0|max:10',
            'category_vii_score' => 'nullable|numeric|min:0|max:5',
            'remarks' => 'nullable|string',
        ]);

        // Check if score already exists
        $existing = ApiFacultyScore::where('faculty_id', $validated['faculty_id'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'API Score already exists for this faculty and academic year!');
        }

        $score = ApiFacultyScore::create($validated);
        $score->calculateTotalScore();

        return redirect()->route('hr.api-scores.show', $score->id)
            ->with('success', 'API Score created successfully!');
    }

    /**
     * Display the specified API score with detailed breakdown
     */
    public function show($id)
    {
        $score = ApiFacultyScore::with([
            'faculty',
            'academicYear',
            'categoryScores',
            'verifiedByUser'
        ])->findOrFail($id);

        $publications = ApiPublication::where('faculty_id', $score->faculty_id)
            ->where('academic_year_id', $score->academic_year_id)
            ->get();

        $activities = ApiActivity::where('faculty_id', $score->faculty_id)
            ->where('academic_year_id', $score->academic_year_id)
            ->get();

        return view('hr.api_scores.show', compact('score', 'publications', 'activities'));
    }

    /**
     * Show the form for editing the specified API score
     */
    public function edit($id)
    {
        $score = ApiFacultyScore::with(['faculty', 'academicYear'])->findOrFail($id);
        $academicYears = ApiAcademicYear::orderBy('start_date', 'desc')->get();

        return view('hr.api_scores.edit', compact('score', 'academicYears'));
    }

    /**
     * Update the specified API score
     */
    public function update(Request $request, $id)
    {
        $score = ApiFacultyScore::findOrFail($id);

        $validated = $request->validate([
            'category_i_score' => 'nullable|numeric|min:0|max:10',
            'category_ii_score' => 'nullable|numeric|min:0|max:25',
            'category_iii_score' => 'nullable|numeric|min:0|max:10',
            'category_iv_score' => 'nullable|numeric|min:0|max:25',
            'category_v_score' => 'nullable|numeric|min:0|max:15',
            'category_vi_score' => 'nullable|numeric|min:0|max:10',
            'category_vii_score' => 'nullable|numeric|min:0|max:5',
            'remarks' => 'nullable|string',
            'status' => 'nullable|in:draft,final',
        ]);

        $score->update($validated);
        $score->calculateTotalScore();

        return redirect()->route('hr.api-scores.show', $score->id)
            ->with('success', 'API Score updated successfully!');
    }

    /**
     * Remove the specified API score
     */
    public function destroy($id)
    {
        $score = ApiFacultyScore::findOrFail($id);
        $score->delete();

        return redirect()->route('hr.api-scores.index')
            ->with('success', 'API Score deleted successfully!');
    }

    /**
     * Mark API score as final
     */
    public function markFinal($id)
    {
        $score = ApiFacultyScore::findOrFail($id);
        $score->status = 'final';
        $score->save();

        return redirect()->route('hr.api-scores.show', $score->id)
            ->with('success', 'API Score marked as final!');
    }

    /**
     * Faculty-wise performance report
     */
    public function facultyReport(Request $request, $facultyId)
    {
        $faculty = Faculty::findOrFail($facultyId);
        $scores = ApiFacultyScore::where('faculty_id', $facultyId)
            ->with('academicYear')
            ->orderBy('created_at', 'desc')
            ->get();

        $academicYears = ApiAcademicYear::orderBy('start_date', 'desc')->get();

        $viewPrefix = $request->route()->getPrefix() === '/principal' ? 'principal' : 'hr';
        return view($viewPrefix . '.api_scores.faculty_report', compact('faculty', 'scores', 'academicYears'));
    }

    /**
     * Manage academic years
     */
    public function academicYears()
    {
        $years = ApiAcademicYear::orderBy('start_date', 'desc')->paginate(20);
        return view('hr.api_scores.academic_years', compact('years'));
    }

    /**
     * Store new academic year
     */
    public function storeAcademicYear(Request $request)
    {
        $validated = $request->validate([
            'year_name' => 'required|string|max:50|unique:api_academic_years,year_name',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,closed',
        ]);

        // If setting as active, deactivate all others
        if ($validated['status'] === 'active') {
            ApiAcademicYear::where('status', 'active')->update(['status' => 'closed']);
        }

        ApiAcademicYear::create($validated);

        return redirect()->route('hr.api-scores.academic-years')
            ->with('success', 'Academic year created successfully!');
    }

    /**
     * Display API score reports and analytics
     */
    public function reports(Request $request)
    {
        $selectedYear = $request->get('academic_year_id') ?? ApiAcademicYear::getActive()?->id;
        $academicYears = ApiAcademicYear::orderBy('start_date', 'desc')->get();

        $stats = [];
        if ($selectedYear) {
            $stats = [
                'total_faculties' => ApiFacultyScore::where('academic_year_id', $selectedYear)->count(),
                'final' => ApiFacultyScore::where('academic_year_id', $selectedYear)->where('status', 'final')->count(),
                'draft' => ApiFacultyScore::where('academic_year_id', $selectedYear)->where('status', 'draft')->count(),
                'average_score' => ApiFacultyScore::where('academic_year_id', $selectedYear)->avg('total_score'),
                'highest_score' => ApiFacultyScore::where('academic_year_id', $selectedYear)->max('total_score'),
                'lowest_score' => ApiFacultyScore::where('academic_year_id', $selectedYear)->min('total_score'),
            ];

            $topScorers = ApiFacultyScore::with('faculty')
                ->where('academic_year_id', $selectedYear)
                ->orderBy('total_score', 'desc')
                ->limit(10)
                ->get();
        } else {
            $topScorers = collect();
        }

        $viewPrefix = $request->route()->getPrefix() === '/principal' ? 'principal' : 'hr';
        return view($viewPrefix . '.api_scores.reports', compact('stats', 'topScorers', 'academicYears', 'selectedYear'));
    }
}
