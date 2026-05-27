<?php

namespace App\Http\Controllers;

use App\Models\HrGradeLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HrGradeLevelController extends Controller
{
    public function index()
    {
        $gradeLevels = HrGradeLevel::with('creator')
            ->withCount('faculties')
            ->ordered()
            ->get();

        return view('hr.grade-levels.index', compact('gradeLevels'));
    }

    public function create()
    {
        return view('hr.grade-levels.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:hr_grade_levels,name',
            'code' => 'nullable|string|max:50|unique:hr_grade_levels,code',
            'description' => 'nullable|string',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gte:min_salary',
            'level_order' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['created_by'] = Auth::id();

        HrGradeLevel::create($validated);

        return redirect()->route('hr.grade-levels.index')
            ->with('success', 'Grade level created successfully.');
    }

    public function show(HrGradeLevel $gradeLevel)
    {
        $gradeLevel->load(['faculties', 'creator', 'payMatrices']);
        $gradeLevel->loadCount(['faculties', 'payMatrices']);

        return view('hr.grade-levels.show', compact('gradeLevel'));
    }

    public function edit(HrGradeLevel $gradeLevel)
    {
        return view('hr.grade-levels.edit', compact('gradeLevel'));
    }

    public function update(Request $request, HrGradeLevel $gradeLevel)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:hr_grade_levels,name,' . $gradeLevel->id,
            'code' => 'nullable|string|max:50|unique:hr_grade_levels,code,' . $gradeLevel->id,
            'description' => 'nullable|string',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gte:min_salary',
            'level_order' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $gradeLevel->update($validated);

        return redirect()->route('hr.grade-levels.index')
            ->with('success', 'Grade level updated successfully.');
    }

    public function destroy(HrGradeLevel $gradeLevel)
    {
        if ($gradeLevel->faculties()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete grade level with assigned faculty members.');
        }

        $gradeLevel->delete();

        return redirect()->route('hr.grade-levels.index')
            ->with('success', 'Grade level deleted successfully.');
    }
}
