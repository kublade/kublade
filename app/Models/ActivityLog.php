<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sagalbot\Encryptable\Encryptable;

/**
 * Class ActivityLog.
 *
 * This class is the model for the activity log.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @OA\Schema(
 *     schema="Activity",
 *     type="object",
 *
 *     @OA\Property(property="id", type="string"),
 *     @OA\Property(property="log_name", type="string"),
 *     @OA\Property(property="causer_id", type="string"),
 *     @OA\Property(property="causer_type", type="string"),
 *     @OA\Property(property="event", type="string"),
 *     @OA\Property(property="subject_type", type="string"),
 *     @OA\Property(property="subject_id", type="string"),
 *     @OA\Property(property="properties", type="object"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time"),
 * )
 *
 * @property string $id
 * @property string $log_name
 * @property string $causer_id
 * @property string $causer_type
 * @property string $event
 * @property string $subject_type
 * @property string $subject_id
 * @property string $properties
 * @property string $description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class ActivityLog extends Model
{
    use SoftDeletes;
    use HasUuids;
    use Encryptable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'activity_logs';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be encrypted.
     *
     * @var array
     */
    protected $encryptable = [
        'properties',
    ];

    /**
     * Get the causer of the activity log.
     *
     * @return MorphTo
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the subject of the activity log.
     *
     * @return MorphTo
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
