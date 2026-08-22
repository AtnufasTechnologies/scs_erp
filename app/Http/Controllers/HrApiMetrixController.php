<?php

namespace App\Http\Controllers;

use App\Models\ApiMetrixCategory;
use App\Models\ApiMetrixComponent;
use App\Models\ApiMetrixSubcomponent;
use App\Models\RoleMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    if ($request->filled('show_in_workdiary')) {
      $query->where('show_in_workdiary', (int) $request->show_in_workdiary === 1 ? 1 : 0);
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
      'slug' => 'nullable|string|max:255',
      'description' => 'nullable|string',
      'status' => 'required|in:active,inactive',
      'show_in_workdiary' => 'nullable|boolean',
      'role_ids' => 'required|array|min:1',
      'role_ids.*' => 'required|integer|exists:role_masters,id',
      'components' => 'required|array|min:1',
      'components.*.title' => 'required|string|max:255',
      'components.*.score' => 'required|numeric|min:0',
      'components.*.verifier_role_master_id' => 'required|integer|exists:role_masters,id',
      'components.*.is_active' => 'nullable|boolean',
    ]);

    DB::transaction(function () use ($validated) {
      $resolvedSlug = $this->buildUniqueCategorySlug($validated['slug'] ?? $validated['title']);

      $category = ApiMetrixCategory::create([
        'title' => trim((string) $validated['title']),
        'slug' => $resolvedSlug,
        'description' => $validated['description'] ?? null,
        'status' => $validated['status'],
        'show_in_workdiary' => (int) ($validated['show_in_workdiary'] ?? 1) === 1 ? 1 : 0,
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

    Cache::forget('workdiary_api_metrix_categories');

    return redirect()->route('hr.api-metrix.index')->with('success', 'API Metrix category created successfully.');
  }

  /**
   * Show category details.
   */
  public function show($id)
  {
    $iqacRoleId = $this->getDefaultIqacRoleId();
    $roles = RoleMaster::orderBy('role_name')->get(['id', 'role_name']);

    $category = ApiMetrixCategory::with([
      'roles:id,role_name',
      'components' => function ($q) {
        $q->with([
          'verifierRole:id,role_name',
          'subcomponents' => function ($subQuery) {
            $subQuery->with('verifierRole:id,role_name')->orderBy('sort_order')->orderBy('id');
          }
        ])->orderBy('sort_order')->orderBy('id');
      }
    ])->findOrFail($id);

    return view('hr.api_metrix.show', compact('category', 'roles', 'iqacRoleId'));
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
      'slug' => 'nullable|string|max:255',
      'description' => 'nullable|string',
      'status' => 'required|in:active,inactive',
      'show_in_workdiary' => 'nullable|boolean',
      'role_ids' => 'required|array|min:1',
      'role_ids.*' => 'required|integer|exists:role_masters,id',
      'components' => 'required|array|min:1',
      'components.*.title' => 'required|string|max:255',
      'components.*.score' => 'required|numeric|min:0',
      'components.*.verifier_role_master_id' => 'required|integer|exists:role_masters,id',
      'components.*.is_active' => 'nullable|boolean',
    ]);

    DB::transaction(function () use ($validated, $category) {
      $resolvedSlug = $this->buildUniqueCategorySlug($validated['slug'] ?? $validated['title'], (int) $category->id);

      $category->update([
        'title' => trim((string) $validated['title']),
        'slug' => $resolvedSlug,
        'description' => $validated['description'] ?? null,
        'status' => $validated['status'],
        'show_in_workdiary' => (int) ($validated['show_in_workdiary'] ?? 1) === 1 ? 1 : 0,
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

    Cache::forget('workdiary_api_metrix_categories');

    return redirect()->route('hr.api-metrix.show', $category->id)->with('success', 'API Metrix category updated successfully.');
  }

  /**
   * Delete category.
   */
  public function destroy($id)
  {
    $category = ApiMetrixCategory::with('components')->findOrFail($id);

    DB::transaction(function () use ($category) {
      $componentIds = $category->components->pluck('id')->all();

      if (!empty($componentIds)) {
        ApiMetrixSubcomponent::whereIn('api_metrix_component_id', $componentIds)->delete();
      }

      $category->roles()->detach();
      $category->components()->delete();
      $category->delete();
    });

    Cache::forget('workdiary_api_metrix_categories');

    return redirect()->route('hr.api-metrix.index')->with('success', 'API Metrix category deleted successfully.');
  }

  /**
   * Store a new subcomponent for a component.
   */
  public function storeSubcomponent(Request $request, $componentId)
  {
    $component = ApiMetrixComponent::findOrFail($componentId);
    $iqacRoleId = $this->getDefaultIqacRoleId();

    $validated = $request->validate([
      'title' => 'required|string|max:255',
      'score' => 'required|numeric|min:0',
      'verifier_role_master_id' => 'nullable|integer|exists:role_masters,id',
      'is_active' => 'nullable|boolean',
    ]);

    $nextSort = (int) ApiMetrixSubcomponent::where('api_metrix_component_id', $component->id)->max('sort_order') + 1;

    ApiMetrixSubcomponent::create([
      'api_metrix_component_id' => $component->id,
      'title' => trim((string) $validated['title']),
      'score' => (float) $validated['score'],
      'verifier_role_master_id' => (int) ($validated['verifier_role_master_id'] ?? $iqacRoleId) ?: null,
      'is_active' => (int) ($validated['is_active'] ?? 0) === 1 ? 1 : 0,
      'sort_order' => $nextSort,
    ]);

    return redirect()->route('hr.api-metrix.show', $component->api_metrix_category_id)
      ->with('success', 'Subcomponent added successfully.');
  }

  /**
   * Update an existing subcomponent.
   */
  public function updateSubcomponent(Request $request, $subcomponentId)
  {
    $subcomponent = ApiMetrixSubcomponent::findOrFail($subcomponentId);
    $iqacRoleId = $this->getDefaultIqacRoleId();

    $validated = $request->validate([
      'title' => 'required|string|max:255',
      'score' => 'required|numeric|min:0',
      'verifier_role_master_id' => 'nullable|integer|exists:role_masters,id',
      'is_active' => 'nullable|boolean',
    ]);

    $subcomponent->update([
      'title' => trim((string) $validated['title']),
      'score' => (float) $validated['score'],
      'verifier_role_master_id' => (int) ($validated['verifier_role_master_id'] ?? $iqacRoleId) ?: null,
      'is_active' => (int) ($validated['is_active'] ?? 0) === 1 ? 1 : 0,
    ]);

    return redirect()->route('hr.api-metrix.show', $subcomponent->component->api_metrix_category_id)
      ->with('success', 'Subcomponent updated successfully.');
  }

  /**
   * Delete a subcomponent.
   */
  public function destroySubcomponent($subcomponentId)
  {
    $subcomponent = ApiMetrixSubcomponent::findOrFail($subcomponentId);
    $categoryId = $subcomponent->component->api_metrix_category_id;

    $subcomponent->delete();

    return redirect()->route('hr.api-metrix.show', $categoryId)
      ->with('success', 'Subcomponent deleted successfully.');
  }

  /**
   * Quickly toggle WorkDiary visibility from list cards.
   */
  public function toggleWorkDiaryVisibility(Request $request, $id)
  {
    $category = ApiMetrixCategory::findOrFail($id);

    $validated = $request->validate([
      'show_in_workdiary' => 'required|boolean',
    ]);

    $category->update([
      'show_in_workdiary' => (int) $validated['show_in_workdiary'] === 1 ? 1 : 0,
      'updated_by' => auth()->id(),
    ]);

    Cache::forget('workdiary_api_metrix_categories');

    return redirect()->back()->with('success', 'WorkDiary visibility updated.');
  }

  private function getDefaultIqacRoleId(): ?int
  {
    $iqacRole = RoleMaster::query()
      ->where(function ($query) {
        $query->whereRaw('LOWER(role_name) = ?', ['iqac'])
          ->orWhereRaw('LOWER(slug) = ?', ['iqac'])
          ->orWhereRaw('LOWER(role_name) like ?', ['%iqac%'])
          ->orWhereRaw('LOWER(slug) like ?', ['%iqac%']);
      })
      ->orderBy('id')
      ->first(['id']);

    return $iqacRole?->id;
  }

  private function buildUniqueCategorySlug(string $slugInput, ?int $ignoreId = null): string
  {
    $base = Str::slug(trim($slugInput));
    if ($base === '') {
      $base = 'category';
    }

    $base = mb_substr($base, 0, 220);
    $slug = $base;
    $counter = 2;

    while (
      ApiMetrixCategory::query()
      ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
      ->where('slug', $slug)
      ->exists()
    ) {
      $slug = $base . '-' . $counter;
      $counter++;
    }

    return $slug;
  }
}
