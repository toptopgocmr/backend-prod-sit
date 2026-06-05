<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            self::log('created', $model, 'Création : ' . class_basename($model));
        });

        static::updated(function ($model) {
            self::log('updated', $model, 'Modification : ' . class_basename($model));
        });

        static::deleted(function ($model) {
            self::log('deleted', $model, 'Suppression : ' . class_basename($model));
        });
    }

    private static function log(string $action, $model, string $description): void
    {
        if (!auth()->check()) return;

        try {
            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => $action,
                'model_type'  => get_class($model),
                'model_id'    => $model->getKey(),
                'description' => $description,
                'ip_address'  => request()->ip(),
                'user_agent'  => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Silently fail — ne pas bloquer l'action principale
        }
    }
}
