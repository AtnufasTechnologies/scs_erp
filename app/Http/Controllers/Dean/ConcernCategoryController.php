<?php

namespace App\Http\Controllers\Dean;

use App\Http\Controllers\Controller;
use App\Models\DsaConcernCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConcernCategoryController extends Controller
{
  public function index()
  {
    $categories = DsaConcernCategory::orderBy('sort_order')->orderBy('name')->get();
    return view('student-affairs.counselling.concern-categories', compact('categories'));
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:100|unique:dsa_concern_categories,name',
      'description' => 'nullable|string|max:1000',
      'sort_order' => 'nullable|integer|min:0',
    ]);

    DsaConcernCategory::create([
      'name' => trim($validated['name']),
      'description' => $validated['description'] ?? null,
      'sort_order' => (int) ($validated['sort_order'] ?? 0),
      'is_active' => true,
      'created_by' => auth()->id(),
      'updated_by' => auth()->id(),
    ]);

    return back()->with('success', 'Concern category created successfully.');
  }

  public function update(Request $request, DsaConcernCategory $category)
  {
    $validated = $request->validate([
      'name' => [
        'required',
        'string',
        'max:100',
        Rule::unique('dsa_concern_categories', 'name')->ignore($category->id),
      ],
      'description' => 'nullable|string|max:1000',
      'sort_order' => 'nullable|integer|min:0',
    ]);

    $category->update([
      'name' => trim($validated['name']),
      'description' => $validated['description'] ?? null,
      'sort_order' => (int) ($validated['sort_order'] ?? 0),
      'updated_by' => auth()->id(),
    ]);

    return back()->with('success', 'Concern category updated.');
  }

  public function toggle(DsaConcernCategory $category)
  {
    $category->update([
      'is_active' => !$category->is_active,
      'updated_by' => auth()->id(),
    ]);

    return back()->with('success', 'Concern category status updated.');
  }
}
