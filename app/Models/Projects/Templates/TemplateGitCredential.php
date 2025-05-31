<?php

declare(strict_types=1);

namespace App\Models\Projects\Templates;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sagalbot\Encryptable\Encryptable;

/**
 * Class TemplateGitCredential.
 *
 * This class is the model for git credentials.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string      $id
 * @property string      $template_id
 * @property string      $url
 * @property string      $branch
 * @property string|null $credentials
 * @property string      $username
 * @property string      $email
 * @property string      $base_path
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon      $deleted_at
 */
class TemplateGitCredential extends Model
{
    use Encryptable;
    use SoftDeletes;
    use HasUuids;
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'template_git_credentials';

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
    public function template(): HasOne
    {
        return $this->hasOne(Template::class, 'id', 'template_id');
    }
}
