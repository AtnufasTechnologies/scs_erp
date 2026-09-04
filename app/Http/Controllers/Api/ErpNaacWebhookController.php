<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ErpNaacWebhookController extends Controller
{
  public function ping(): JsonResponse
  {
    return response()->json([
      'ok' => true,
      'service' => 'erp-naac-webhook',
      'timestamp' => now()->toIso8601String(),
    ]);
  }

  public function snapshot(): JsonResponse
  {
    return response()->json([
      'ok' => true,
      'data' => [
        'cycles' => DB::table('naac_cycles')->orderBy('id')->get(),
        'sessions' => DB::table('naac_aqar_sessions')->orderBy('id')->get(),
        'supporting_docs' => DB::table('naac_supporting_docs')->orderBy('id')->get(),
        'multi_docs' => DB::table('naac_multi_docs')->orderBy('id')->get(),
        'criterian_docs' => DB::table('naac_criterian_docs')->orderBy('id')->get(),
        'multi_doc_items' => DB::table('naac_multi_doc_items')->orderBy('id')->get(),
        'criterian_doc_items' => DB::table('naac_criterian_doc_items')->orderBy('id')->get(),
        'single_content' => DB::table('naac_single_contents')->orderByDesc('id')->first(),
      ],
    ]);
  }

  public function listCycles(Request $request): JsonResponse
  {
    $query = DB::table('naac_cycles')->orderBy('sort_order')->orderBy('id');

    if ($request->has('active')) {
      $query->where('is_active', $request->boolean('active'));
    }

    $cycles = $query->get();

    return response()->json([
      'ok' => true,
      'count' => $cycles->count(),
      'data' => $cycles,
    ]);
  }

  public function listCyclesFull(Request $request): JsonResponse
  {
    $cyclesQuery = DB::table('naac_cycles')->orderBy('sort_order')->orderBy('id');

    if ($request->has('active')) {
      $cyclesQuery->where('is_active', $request->boolean('active'));
    }

    $cycles = $cyclesQuery->get();

    $sessions = DB::table('naac_aqar_sessions')
      ->orderBy('sort_order')
      ->orderBy('id')
      ->get();

    if ($request->has('active')) {
      $sessions = $sessions->where('is_active', $request->boolean('active'))->values();
    }

    $supportingDocs = DB::table('naac_supporting_docs')->orderBy('sort_order')->orderBy('id')->get();
    $multiDocs = DB::table('naac_multi_docs')->orderBy('sort_order')->orderBy('id')->get();
    $multiDocItems = DB::table('naac_multi_doc_items')->orderBy('sort_order')->orderBy('id')->get();
    $criterianDocs = DB::table('naac_criterian_docs')->orderBy('sort_order')->orderBy('id')->get();
    $criterianDocItems = DB::table('naac_criterian_doc_items')->orderBy('sort_order')->orderBy('id')->get();

    if ($request->has('active')) {
      $activeOnly = $request->boolean('active');
      $supportingDocs = $supportingDocs->where('is_active', $activeOnly)->values();
      $multiDocs = $multiDocs->where('is_active', $activeOnly)->values();
      $multiDocItems = $multiDocItems->where('is_active', $activeOnly)->values();
      $criterianDocs = $criterianDocs->where('is_active', $activeOnly)->values();
      $criterianDocItems = $criterianDocItems->where('is_active', $activeOnly)->values();
    }

    $multiDocItemsByDoc = $multiDocItems->groupBy('multi_doc_id');
    $criterianDocItemsByDoc = $criterianDocItems->groupBy('criterian_doc_id');

    $multiDocsBySession = $multiDocs->groupBy('session_id')->map(function ($docs) use ($multiDocItemsByDoc) {
      return $docs->map(function ($doc) use ($multiDocItemsByDoc) {
        $doc->items = ($multiDocItemsByDoc[$doc->id] ?? collect())->values();
        return $doc;
      })->values();
    });

    $criterianDocsBySession = $criterianDocs->groupBy('session_id')->map(function ($docs) use ($criterianDocItemsByDoc) {
      return $docs->map(function ($doc) use ($criterianDocItemsByDoc) {
        $doc->items = ($criterianDocItemsByDoc[$doc->id] ?? collect())->values();
        return $doc;
      })->values();
    });

    $supportingDocsBySession = $supportingDocs->groupBy('session_id');
    $sessionsByCycle = $sessions->groupBy('cycle_id');

    $fullCycles = $cycles->map(function ($cycle) use ($sessionsByCycle, $supportingDocsBySession, $multiDocsBySession, $criterianDocsBySession) {
      $cycleSessions = ($sessionsByCycle[$cycle->id] ?? collect())->map(function ($session) use ($supportingDocsBySession, $multiDocsBySession, $criterianDocsBySession) {
        $session->supporting_docs = ($supportingDocsBySession[$session->id] ?? collect())->values();
        $session->multi_docs = ($multiDocsBySession[$session->id] ?? collect())->values();
        $session->criterian_docs = ($criterianDocsBySession[$session->id] ?? collect())->values();
        return $session;
      })->values();

      $cycle->sessions = $cycleSessions;

      return $cycle;
    })->values();

    return response()->json([
      'ok' => true,
      'count' => $fullCycles->count(),
      'data' => $fullCycles,
      'single_content' => DB::table('naac_single_contents')->orderByDesc('id')->first(),
    ]);
  }

  public function webhook(Request $request): JsonResponse
  {
    $payload = $request->all();

    if (isset($payload['events']) && is_array($payload['events'])) {
      $result = DB::transaction(function () use ($payload) {
        $responses = [];

        foreach ($payload['events'] as $index => $event) {
          $resource = (string) ($event['resource'] ?? '');
          $operation = (string) ($event['operation'] ?? '');
          $data = (array) ($event['data'] ?? []);

          $responses[] = [
            'index' => $index,
            'resource' => $resource,
            'operation' => $operation,
            'result' => $this->applyOperation($resource, $operation, $data),
          ];
        }

        return $responses;
      });

      return response()->json([
        'ok' => true,
        'mode' => 'batch',
        'count' => count($result),
        'results' => $result,
      ]);
    }

    $resource = (string) ($payload['resource'] ?? '');
    $operation = (string) ($payload['operation'] ?? '');
    $data = (array) ($payload['data'] ?? []);

    $result = DB::transaction(function () use ($resource, $operation, $data) {
      return $this->applyOperation($resource, $operation, $data);
    });

    return response()->json([
      'ok' => true,
      'mode' => 'single',
      'result' => $result,
    ]);
  }

  public function upsertCycle(Request $request): JsonResponse
  {
    return response()->json(['ok' => true, 'result' => $this->applyOperation('cycle', 'upsert', $request->all())]);
  }

  public function updateCycle(Request $request, $id): JsonResponse
  {
    $data = $request->all();
    $data['id'] = (int) $id;
    return response()->json(['ok' => true, 'result' => $this->applyOperation('cycle', 'upsert', $data)]);
  }

  public function deleteCycle($id): JsonResponse
  {
    return response()->json(['ok' => true, 'result' => $this->applyOperation('cycle', 'delete', ['id' => (int) $id])]);
  }

  public function upsertSession(Request $request): JsonResponse
  {
    return response()->json(['ok' => true, 'result' => $this->applyOperation('session', 'upsert', $request->all())]);
  }

  public function updateSession(Request $request, $id): JsonResponse
  {
    $data = $request->all();
    $data['id'] = (int) $id;
    return response()->json(['ok' => true, 'result' => $this->applyOperation('session', 'upsert', $data)]);
  }

  public function deleteSession($id): JsonResponse
  {
    return response()->json(['ok' => true, 'result' => $this->applyOperation('session', 'delete', ['id' => (int) $id])]);
  }

  public function upsertSupportingDoc(Request $request): JsonResponse
  {
    return response()->json(['ok' => true, 'result' => $this->applyOperation('supporting_doc', 'upsert', $request->all())]);
  }

  public function updateSupportingDoc(Request $request, $id): JsonResponse
  {
    $data = $request->all();
    $data['id'] = (int) $id;
    return response()->json(['ok' => true, 'result' => $this->applyOperation('supporting_doc', 'upsert', $data)]);
  }

  public function deleteSupportingDoc($id): JsonResponse
  {
    return response()->json(['ok' => true, 'result' => $this->applyOperation('supporting_doc', 'delete', ['id' => (int) $id])]);
  }

  public function upsertMultiDoc(Request $request): JsonResponse
  {
    return response()->json(['ok' => true, 'result' => $this->applyOperation('multi_doc', 'upsert', $request->all())]);
  }

  public function updateMultiDoc(Request $request, $id): JsonResponse
  {
    $data = $request->all();
    $data['id'] = (int) $id;
    return response()->json(['ok' => true, 'result' => $this->applyOperation('multi_doc', 'upsert', $data)]);
  }

  public function deleteMultiDoc($id): JsonResponse
  {
    return response()->json(['ok' => true, 'result' => $this->applyOperation('multi_doc', 'delete', ['id' => (int) $id])]);
  }

  public function upsertCriterianDoc(Request $request): JsonResponse
  {
    return response()->json(['ok' => true, 'result' => $this->applyOperation('criterian_doc', 'upsert', $request->all())]);
  }

  public function updateCriterianDoc(Request $request, $id): JsonResponse
  {
    $data = $request->all();
    $data['id'] = (int) $id;
    return response()->json(['ok' => true, 'result' => $this->applyOperation('criterian_doc', 'upsert', $data)]);
  }

  public function deleteCriterianDoc($id): JsonResponse
  {
    return response()->json(['ok' => true, 'result' => $this->applyOperation('criterian_doc', 'delete', ['id' => (int) $id])]);
  }

  public function upsertMultiDocItem(Request $request): JsonResponse
  {
    return response()->json(['ok' => true, 'result' => $this->applyOperation('multi_doc_item', 'upsert', $request->all())]);
  }

  public function updateMultiDocItem(Request $request, $id): JsonResponse
  {
    $data = $request->all();
    $data['id'] = (int) $id;
    return response()->json(['ok' => true, 'result' => $this->applyOperation('multi_doc_item', 'upsert', $data)]);
  }

  public function deleteMultiDocItem($id): JsonResponse
  {
    return response()->json(['ok' => true, 'result' => $this->applyOperation('multi_doc_item', 'delete', ['id' => (int) $id])]);
  }

  public function upsertCriterianDocItem(Request $request): JsonResponse
  {
    return response()->json(['ok' => true, 'result' => $this->applyOperation('criterian_doc_item', 'upsert', $request->all())]);
  }

  public function updateCriterianDocItem(Request $request, $id): JsonResponse
  {
    $data = $request->all();
    $data['id'] = (int) $id;
    return response()->json(['ok' => true, 'result' => $this->applyOperation('criterian_doc_item', 'upsert', $data)]);
  }

  public function deleteCriterianDocItem($id): JsonResponse
  {
    return response()->json(['ok' => true, 'result' => $this->applyOperation('criterian_doc_item', 'delete', ['id' => (int) $id])]);
  }

  public function createSingleContent(Request $request): JsonResponse
  {
    return response()->json(['ok' => true, 'result' => $this->applyOperation('single_content', 'upsert', $request->all())]);
  }

  public function updateSingleContent(Request $request, $id): JsonResponse
  {
    $data = $request->all();
    $data['id'] = (int) $id;
    return response()->json(['ok' => true, 'result' => $this->applyOperation('single_content', 'update', $data)]);
  }

  private function applyOperation(string $resource, string $operation, array $data): array
  {
    $normalizedResource = $this->normalizeResource($resource);
    $normalizedOperation = strtolower(trim($operation));

    if ($normalizedResource === '') {
      abort(response()->json(['message' => 'Unsupported resource: ' . $resource], 422));
    }

    if (!in_array($normalizedOperation, ['upsert', 'delete', 'update'], true)) {
      abort(response()->json(['message' => 'Unsupported operation: ' . $operation], 422));
    }

    if ($normalizedResource === 'single_content' && $normalizedOperation === 'delete') {
      abort(response()->json(['message' => 'Delete is not supported for single_content.'], 422));
    }

    if ($normalizedOperation === 'delete') {
      $id = (int) ($data['id'] ?? 0);
      if ($id <= 0) {
        abort(response()->json(['message' => 'Missing valid id for delete operation.'], 422));
      }

      $deleted = DB::table($this->tableForResource($normalizedResource))->where('id', $id)->delete();

      return [
        'resource' => $normalizedResource,
        'operation' => 'delete',
        'id' => $id,
        'deleted' => $deleted > 0,
      ];
    }

    return $this->upsertResource($normalizedResource, $data);
  }

  private function upsertResource(string $resource, array $data): array
  {
    $table = $this->tableForResource($resource);
    $payload = $this->sanitizeData($resource, $data);
    $id = (int) ($data['id'] ?? 0);

    if ($resource === 'single_content') {
      if ($id > 0) {
        $exists = DB::table($table)->where('id', $id)->exists();
        if ($exists) {
          DB::table($table)->where('id', $id)->update($payload + ['updated_at' => now()]);
          return ['resource' => $resource, 'operation' => 'upsert', 'id' => $id, 'action' => 'updated'];
        }

        DB::table($table)->insert($payload + ['id' => $id, 'created_at' => now(), 'updated_at' => now()]);
        return ['resource' => $resource, 'operation' => 'upsert', 'id' => $id, 'action' => 'created'];
      }

      $latest = DB::table($table)->orderByDesc('id')->first();
      if ($latest) {
        DB::table($table)->where('id', $latest->id)->update($payload + ['updated_at' => now()]);
        return ['resource' => $resource, 'operation' => 'upsert', 'id' => (int) $latest->id, 'action' => 'updated'];
      }

      $newId = DB::table($table)->insertGetId($payload + ['created_at' => now(), 'updated_at' => now()]);
      return ['resource' => $resource, 'operation' => 'upsert', 'id' => (int) $newId, 'action' => 'created'];
    }

    if ($id > 0) {
      $exists = DB::table($table)->where('id', $id)->exists();
      if ($exists) {
        DB::table($table)->where('id', $id)->update($payload + ['updated_at' => now()]);
        return ['resource' => $resource, 'operation' => 'upsert', 'id' => $id, 'action' => 'updated'];
      }

      DB::table($table)->insert($payload + ['id' => $id, 'created_at' => now(), 'updated_at' => now()]);
      return ['resource' => $resource, 'operation' => 'upsert', 'id' => $id, 'action' => 'created'];
    }

    $newId = DB::table($table)->insertGetId($payload + ['created_at' => now(), 'updated_at' => now()]);

    return ['resource' => $resource, 'operation' => 'upsert', 'id' => (int) $newId, 'action' => 'created'];
  }

  private function tableForResource(string $resource): string
  {
    return match ($resource) {
      'cycle' => 'naac_cycles',
      'session' => 'naac_aqar_sessions',
      'supporting_doc' => 'naac_supporting_docs',
      'multi_doc' => 'naac_multi_docs',
      'criterian_doc' => 'naac_criterian_docs',
      'multi_doc_item' => 'naac_multi_doc_items',
      'criterian_doc_item' => 'naac_criterian_doc_items',
      'single_content' => 'naac_single_contents',
      default => '',
    };
  }

  private function normalizeResource(string $resource): string
  {
    $value = strtolower(trim($resource));

    return match ($value) {
      'cycle', 'iqac_cycle' => 'cycle',
      'session', 'aqar_session' => 'session',
      'supporting_doc' => 'supporting_doc',
      'multi_doc' => 'multi_doc',
      'criterian_doc', 'criterion_doc' => 'criterian_doc',
      'multi_doc_item' => 'multi_doc_item',
      'criterian_doc_item', 'criterion_doc_item' => 'criterian_doc_item',
      'single_content', 'naac_meta', 'iqac_meta' => 'single_content',
      default => '',
    };
  }

  private function sanitizeData(string $resource, array $data): array
  {
    $whitelist = match ($resource) {
      'cycle' => ['name', 'sort_order', 'is_active'],
      'session' => ['cycle_id', 'name', 'sort_order', 'is_active'],
      'supporting_doc' => ['cycle_id', 'session_id', 'title', 'doc_url', 'sort_order', 'is_active'],
      'multi_doc' => ['cycle_id', 'session_id', 'title', 'sort_order', 'is_active'],
      'criterian_doc' => ['cycle_id', 'session_id', 'criterion_code', 'title', 'sort_order', 'is_active'],
      'multi_doc_item' => ['multi_doc_id', 'title', 'doc_url', 'sort_order', 'is_active'],
      'criterian_doc_item' => ['criterian_doc_id', 'title', 'doc_url', 'sort_order', 'is_active'],
      'single_content' => ['naac_certificate_pic', 'naac_quality_pic', 'autonomy_doc', 'ssr_title', 'ssr_doc', 'ubpeer_doc', 'iqac_composition'],
      default => [],
    };

    $normalized = [];

    if (isset($data['name']) && !isset($data['title']) && in_array('title', $whitelist, true)) {
      $data['title'] = $data['name'];
    }

    if ($resource === 'criterian_doc') {
      if (isset($data['criterion']) && !isset($data['criterion_code'])) {
        $data['criterion_code'] = $data['criterion'];
      }
      if (isset($data['criterian']) && !isset($data['criterion_code'])) {
        $data['criterion_code'] = $data['criterian'];
      }
    }

    foreach ($whitelist as $field) {
      if (array_key_exists($field, $data)) {
        $normalized[$field] = $data[$field];
      }
    }

    $extra = $data;
    unset($extra['id']);
    foreach ($whitelist as $field) {
      unset($extra[$field]);
    }
    if (!empty($extra)) {
      $normalized['payload'] = json_encode($extra);
    }

    return $normalized;
  }
}
