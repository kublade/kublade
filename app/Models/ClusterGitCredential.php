<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sagalbot\Encryptable\Encryptable;

/**
 * Class ClusterGitCredential.
 *
 * This class is the model for git credentials.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $cluster_id
 * @property string $url
 * @property string $branch
 * @property string $credentials
 * @property string $username
 * @property string $email
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class ClusterGitCredential extends Model
{
    use Encryptable;
    use SoftDeletes;
    use HasUuids;

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
     * @var array<string>
     */
    protected $encryptable = [
        'credentials',
    ];

    /**
     * Relation to cluster.
     *
     * @return HasOne
     */
    public function cluster(): HasOne
    {
        return $this->hasOne(Cluster::class, 'id', 'cluster_id');
    }
}
