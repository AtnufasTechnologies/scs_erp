<?php

namespace App\Http\Controllers;

use App\Models\ShiftMaster;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ShiftMasterController extends Controller
{
  public function index()
  {
    $data = ShiftMaster::orderBy('sort_order')->orderBy('title')->get();

    return view('admin.master.shift-master', [
      'data' => $data,
    ]);
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'title' => 'required|string|max:100',
      'slug' => 'nullable|string|max:100|alpha_dash|unique:shift_masters,slug',
      'sort_order' => 'nullable|integer|min:0',
    ]);

    $slugBase = $validated['slug'] ?? Str::slug($validated['title']);
    $slug = $this->resolveUniqueSlug($slugBase);

    ShiftMaster::create([
      'title' => $validated['title'],
      'slug' => $slug,
      'is_active' => 1,
      'is_system' => 0,
      'sort_order' => $validated['sort_order'] ?? 100,
    ]);

    return redirect()->back()->with('success', 'Shift created successfully.');
  }

  public function update(Request $request, $id)
  {
    $shift = ShiftMaster::findOrFail($id);

    $validated = $request->validate([
      'title' => 'required|string|max:100',
      'sort_order' => 'nullable|integer|min:0',
    ]);

    $shift->title = $validated['title'];
    $shift->sort_order = $validated['sort_order'] ?? $shift->sort_order;
    $shift->save();

    return redirect()->back()->with('success', 'Shift updated successfully.');
  }

  public function toggle($id)
  {
    $shift = ShiftMaster::findOrFail($id);

    if ($shift->is_active && ShiftMaster::where('is_active', 1)->count() <= 1) {
      return redirect()->back()->with('error', 'At least one active shift is required.');
    }

    $shift->is_active = $shift->is_active ? 0 : 1;
    $shift->save();

    return redirect()->back()->with('success', 'Shift status updated successfully.');
  }

  private function resolveUniqueSlug(string $base): string
  {
    $slug = trim($base) !== '' ? Str::slug($base) : 'shift';
    $candidate = $slug;
    $i = 1;

    while (ShiftMaster::where('slug', $candidate)->exists()) {
      $candidate = $slug . '-' . $i;
      $i++;
    }

    return $candidate;
  }
}
