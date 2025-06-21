<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * Trait LogsActivity.
 *
 * This trait is used to log the activity of the model.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
trait LogsActivity
{
    /**
     * Boot the logs activity.
     */
    public static function bootLogsActivity()
    {
        foreach (['created', 'updated', 'deleted'] as $event) {
            static::$event(function ($model) use ($event) {
                $model->logActivity($event);
            });
        }

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restored(function ($model) {
                $model->logActivity('restored');
            });
        }
    }

    /**
     * Log the activity.
     *
     * @param string $event
     */
    protected function logActivity(string $event)
    {
        try {
            $changes = $this->getActivityChanges($event);

            ActivityLog::create([
                'log_name'     => class_basename($this),
                'causer_id'    => optional(Auth::user())->id,
                'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
                'event'        => $event,
                'subject_type' => get_class($this),
                'subject_id'   => $this->getKey(),
                'properties'   => $changes,
                'description'  => $this->getActivityDescription($event, $changes),
            ]);
        } catch (Throwable $th) {
        }
    }

    /**
     * Get the changes of the activity.
     *
     * @param string $event
     *
     * @return array
     */
    protected function getActivityChanges(string $event): array
    {
        switch ($event) {
            case 'created':
                return ['attributes' => $this->getAttributes()];
            case 'updated':
                return [
                    'old'        => $this->getOriginal(),
                    'attributes' => $this->getDirty(),
                ];
            case 'deleted':
                return ['attributes' => $this->getAttributes()];
            case 'restored':
                return ['attributes' => $this->getAttributes()];
            default:
                return [];
        }
    }

    /**
     * Get the description of the activity.
     *
     * @param string $event
     * @param array  $changes
     *
     * @return string
     */
    protected function getActivityDescription(string $event, array $changes): string
    {
        return ucfirst($event) . ' ' . class_basename($this) . ' (ID: ' . $this->getKey() . ')';
    }
}
