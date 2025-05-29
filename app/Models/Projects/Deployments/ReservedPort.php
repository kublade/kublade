<?php

declare(strict_types=1);

namespace App\Models\Projects\Deployments;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ReservedPort.
 *
 * This class is the model for reserved deployment ports.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string      $id
 * @property string      $deployment_id
 * @property string|null $group
 * @property string|null $claim
 * @property int         $port
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon      $deleted_at
 */
class ReservedPort extends Model
{
    use SoftDeletes;
    use HasUuids;
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reserved_ports';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * List of all ports disallowed for service usage.
     *
     * @param mixed $group
     */
    public static function disallowed($group = 'services')
    {
        return collect(
            self::where('group', '=', $group)
                ->get()
                ->pluck('port')
        );
    }

    /**
     * Get random available port.
     *
     * @param mixed      $group
     * @param mixed|null $disallowed
     */
    public static function random($group = 'services', $disallowed = null)
    {
        $fromPort = 49152;
        $toPort   = 65535;

        if (!$disallowed) {
            $disallowed = self::disallowed($group);

            if ($disallowed->count() >= $toPort - $fromPort) {
                return null;
            }
        }

        return collect(range($fromPort, $toPort))->filter(function (int $port) use ($disallowed) {
            return !$disallowed->contains($port);
        })->random();
    }

    /**
     * Relation to deployment.
     *
     * @return HasOne
     */
    public function deployment(): HasOne
    {
        return $this->hasOne(Deployment::class, 'id', 'deployment_id');
    }
}
