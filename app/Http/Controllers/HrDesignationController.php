<?php

namespace App\Http\Controllers;

use App\Models\HrDesignation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HrDesignationController extends Controller
{
    public function index()
    {
        $designations = HrDesignation::with('creator')
            ->withCount('faculties')
            ->ordered()
            ->get();

        return view('hr.designations.index', compact('designations'));
    }

    public function create()
    {
        return view('hr.designations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:hr_designations,name',
            'code' => 'nullable|string|max:50|unique:hr_designations,code',
            'description' => 'nullable|string',
            'category' => 'required|in:teaching,non-teaching,administrative,technical,support',
            'hierarchy_level' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['created_by'] = Auth::id();

        HrDesignation::create($validated);

        return redirect()->route('hr.designations.index')
            ->with('success', 'Designation created successfully.');
    }

    public function show(HrDesignation $designation)
    {
        $designation->load(['faculties', 'creator', 'payMatrices']);
        $designation->loadCount(['faculties', 'payMatrices']);

        return view('hr.designations.show', compact('designation'));
    }

    public function edit(HrDesignation $designation)
    {
        return view('hr.designations.edit', compact('designation'));
    }

    public function update(Request $request, HrDesignation $designation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:hr_designations,name,' . $designation->id,
            'code' => 'nullable|string|max:50|unique:hr_designations,code,' . $designation->id,
            'description' => 'nullable|string',
            'category' => 'required|in:teaching,non-teaching,administrative,technical,support',
            'hierarchy_level' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $designation->update($validated);

        return redirect()->route('hr.designations.index')
            ->with('success', 'Designation updated successfully.');
    }

    public function destroy(HrDesignation $designation)
    {
        if ($designation->faculties()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete designation with assigned faculty members.');
        }

        $designation->delete();

        return redirect()->route('hr.designations.index')
            ->with('success', 'Designation deleted successfully.');
    }
}
