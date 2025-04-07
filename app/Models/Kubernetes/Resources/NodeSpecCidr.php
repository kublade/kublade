<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Resources;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class NodeSpecCidr.
 *
 * This class is the model for kubernetes node spec cidr's.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $cidr
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class NodeSpecCidr extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'node_spec_cidrs';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Relation to node.
     *
     * @return HasOne
     */
    public function spec(): HasOne
    {
        return $this->hasOne(NodeSpec::class, 'id', 'node_spec_id');
    }
}
