<?php

namespace App\Services;

use App\Models\AdmissionRegistration;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class UserActivityLogger
{
  protected static ?bool $tableExists = null;

  protected static array $maskedFields = [
    'password',
    'remember_token',
    'token',
    'api_token',
    'otp',
  ];

  public static function log(string $event, Model $model): void
  {
    if (!self::shouldLog($model)) {
      return;
    }

    if (!self::activityTableExists()) {
      return;
    }

    // Don't log for admission routes (applicant actions - not users in users table)
    // Covers /erp/new-admission/* and /erp/admission/* route groups
    if (
      request()->is('erp/new-admission*') ||
      request()->is('erp/admission*') ||
      request()->is('*/erp/new-admission*') ||
      request()->is('*/erp/admission*')
    ) {
      return;
    }

    [$oldValues, $newValues] = self::resolvePayload($event, $model);


    // Ensure user is authenticated before logging
    DB::table('user_activity_logs')->insert([
      'user_id' => Auth::id(),
      'event' => $event,
      'auditable_type' => $model::class,
      'auditable_id' => (string) $model->getKey(),
      'description' => self::description($event, $model),
      'ip_address' => request()?->ip(),
      'method' => request()?->method(),
      'url' => request()?->fullUrl(),
      'user_agent' => request()?->userAgent(),
      'old_values' => empty($oldValues) ? null : json_encode($oldValues),
      'new_values' => empty($newValues) ? null : json_encode($newValues),
      'created_at' => now(),
    ]);
  }

  protected static function shouldLog(Model $model): bool
  {
    if ($model instanceof UserActivityLog) {
      return false;
    }

    return str_starts_with($model::class, 'App\\Models\\');
  }

  protected static function activityTableExists(): bool
  {
    if (self::$tableExists !== null) {
      return self::$tableExists;
    }

    try {
      self::$tableExists = Schema::hasTable('user_activity_logs');
    } catch (Throwable) {
      self::$tableExists = false;
    }

    return self::$tableExists;
  }

  protected static function resolvePayload(string $event, Model $model): array
  {
    if ($event === 'created') {
      return [
        [],
        self::sanitize($model->getAttributes()),
      ];
    }

    if ($event === 'deleted') {
      return [
        self::sanitize($model->getOriginal()),
        [],
      ];
    }

    $changes = Arr::except($model->getChanges(), ['updated_at']);
    if (empty($changes)) {
      return [[], []];
    }

    $original = [];
    foreach (array_keys($changes) as $key) {
      $original[$key] = $model->getOriginal($key);
    }

    return [
      self::sanitize($original),
      self::sanitize($changes),
    ];
  }

  protected static function sanitize(array $values): array
  {
    foreach ($values as $key => $value) {
      if (in_array(strtolower((string) $key), self::$maskedFields, true)) {
        $values[$key] = '[REDACTED]';
      }
    }

    return $values;
  }

  protected static function description(string $event, Model $model): string
  {
    $modelName = class_basename($model);

    return ucfirst($event) . ' ' . $modelName;
  }
}
