<?php

namespace App\Providers;

use App\Services\UserActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen('eloquent.created: *', function (string $eventName, array $data) {
            $model = $data[0] ?? null;
            if ($model instanceof Model) {
                UserActivityLogger::log('created', $model);
            }
        });

        Event::listen('eloquent.updated: *', function (string $eventName, array $data) {
            $model = $data[0] ?? null;
            if ($model instanceof Model) {
                UserActivityLogger::log('updated', $model);
            }
        });

        Event::listen('eloquent.deleted: *', function (string $eventName, array $data) {
            $model = $data[0] ?? null;
            if ($model instanceof Model) {
                UserActivityLogger::log('deleted', $model);
            }
        });
    }
}
