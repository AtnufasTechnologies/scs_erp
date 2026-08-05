<?php

namespace App\Http\Controllers;

use App\Models\ApiMetrixCategory;
use App\Models\RoleMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HrApiMetrixController extends Controller
{
  /**
   * Display a listing of API Metrix categories.
   */
  public function index(Request $request)
  {
    $query = ApiMetrixCategory::withCount(['components', 'roles'])
      ->with(['roles:id,role_name'])
      ->orderByDesc('id');

    if ($request->filled('search')) {
      $search = trim((string) $request->search);
      $query->where('title', 'like', "%{$search}%");
    }

    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    if ($request->filled('role_id')) {
      $roleId = (int) $request->role_id;
      $query->whereHas('roles', function ($q) use ($roleId) {
        $q->where('role_masters.id', $roleId);
      });
    }

    $categories = $query->paginate(20)->withQueryString();
    $roles = RoleMaster::orderBy('role_name')->get(['id', 'role_name']);

    return view('hr.api_metrix.index', compact('categories', 'roles'));
  }

  /**
   * Show form to create a category.
   */
  public function create()
  {
    $roles = RoleMaster::orderBy('role_name')->get(['id', 'role_name']);

    return view('hr.api_metrix.create', compact('roles'));
  }

  /**
   * Store category with components and applicable roles.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'title' => 'required|string|max:255|unique:api_metrix_categories,title',
      'description' => 'nullable|string',
      'status' => 'required|in:active,inactive',
      'role_ids' => 'required|array|min:1',
      'role_ids.*' => 'required|integer|exists:role_masters,id',
      'components' => 'required|array|min:1',
      'components.*.title' => 'required|string|max:255',
      'components.*.score' => 'required|numeric|min:0',
      'components.*.verifier_role_master_id' => 'required|integer|exists:role_masters,id',
      'components.*.is_active' => 'nullable|boolean',
    ]);

    DB::transaction(function () use ($validated) {
      $category = ApiMetrixCategory::create([
        'title' => trim((string) $validated['title']),
        'description' => $validated['description'] ?? null,
        'status' => $validated['status'],
        'created_by' => auth()->id(),
        'updated_by' => auth()->id(),
      ]);

      $category->roles()->sync(collect($validated['role_ids'])->map(fn($id) => (int) $id)->unique()->values()->all());

      $components = collect($validated['components'])
        ->values()
        ->map(function ($component, $index) {
          return [
            'title' => trim((string) ($component['title'] ?? '')),
            'score' => (float) ($component['score'] ?? 0),
            'verifier_role_master_id' => (int) ($component['verifier_role_master_id'] ?? 0),
            'is_active' => (int) ($component['is_active'] ?? 0) === 1 ? 1 : 0,
            'sort_order' => $index + 1,
          ];
        })
        ->filter(fn($component) => $component['title'] !== '')
        ->values()
        ->all();

      $category->components()->createMany($components);
    });

    return redirect()->route('hr.api-metrix.index')->with('success', 'API Metrix category created successfully.');
  }

  /**
   * Show category details.
   */
  public function show($id)
  {
    $category = ApiMetrixCategory::with([
      'roles:id,role_name',
      'components' => function ($q) {
        $q->with('verifierRole:id,role_name')->orderBy('sort_order')->orderBy('id');
      }
    ])->findOrFail($id);

    return view('hr.api_metrix.show', compact('category'));
  }

  /**
   * Show form to edit a category.
   */
  public function edit($id)
  {
    $category = ApiMetrixCategory::with([
      'roles:id,role_name',
      'components' => function ($q) {
        $q->orderBy('sort_order')->orderBy('id');
      }
    ])->findOrFail($id);

    $roles = RoleMaster::orderBy('role_name')->get(['id', 'role_name']);

    return view('hr.api_metrix.edit', compact('category', 'roles'));
  }

  /**
   * Update category with components and applicable roles.
   */
  public function update(Request $request, $id)
  {
    $category = ApiMetrixCategory::findOrFail($id);

    $validated = $request->validate([
      'title' => 'required|string|max:255|unique:api_metrix_categories,title,' . $category->id,
      'description' => 'nullable|string',
      'status' => 'required|in:active,inactive',
      'role_ids' => 'required|array|min:1',
      'role_ids.*' => 'required|integer|exists:role_masters,id',
      'components' => 'required|array|min:1',
      'components.*.title' => 'required|string|max:255',
      'components.*.score' => 'required|numeric|min:0',
      'components.*.verifier_role_master_id' => 'required|integer|exists:role_masters,id',
      'components.*.is_active' => 'nullable|boolean',
    ]);

    DB::transaction(function () use ($validated, $category) {
      $category->update([
        'title' => trim((string) $validated['title']),
        'description' => $validated['description'] ?? null,
        'status' => $validated['status'],
        'updated_by' => auth()->id(),
      ]);

      $category->roles()->sync(collect($validated['role_ids'])->map(fn($id) => (int) $id)->unique()->values()->all());

      $category->components()->delete();

      $components = collect($validated['components'])
        ->values()
        ->map(function ($component, $index) {
          return [
            'title' => trim((string) ($component['title'] ?? '')),
            'score' => (float) ($component['score'] ?? 0),
            'verifier_role_master_id' => (int) ($component['verifier_role_master_id'] ?? 0),
            'is_active' => (int) ($component['is_active'] ?? 0) === 1 ? 1 : 0,
            'sort_order' => $index + 1,
          ];
        })
        ->filter(fn($component) => $component['title'] !== '')
        ->values()
        ->all();

      $category->components()->createMany($components);
    });

    return redirect()->route('hr.api-metrix.show', $category->id)->with('success', 'API Metrix category updated successfully.');
  }

  /**
   * Delete category.
   */
  public function destroy($id)
  {
    $category = ApiMetrixCategory::with('components')->findOrFail($id);

    DB::transaction(function () use ($category) {
      $category->roles()->detach();
      $category->components()->delete();
      $category->delete();
    });

    return redirect()->route('hr.api-metrix.index')->with('success', 'API Metrix category deleted successfully.');
  }
}
