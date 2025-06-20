<?php

declare(strict_types=1);

namespace App\Models\Projects\Templates;

use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TemplateField.
 *
 * This class is the model for template fields.
 *
 * @OA\Schema(
 *     schema="TemplateField",
 *     type="object",
 *
 *     @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="template_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="advanced", type="boolean", example=false),
 *     @OA\Property(property="type", type="string", example="text"),
 *     @OA\Property(property="required", type="boolean", example=false),
 *     @OA\Property(property="secret", type="boolean", example=false),
 *     @OA\Property(property="label", type="string", example="Label"),
 *     @OA\Property(property="key", type="string", example="key"),
 *     @OA\Property(property="value", type="string", example="Value", nullable=true),
 *     @OA\Property(property="min", type="number", example=0, nullable=true),
 *     @OA\Property(property="max", type="number", example=0, nullable=true),
 *     @OA\Property(property="step", type="number", example=0, nullable=true),
 *     @OA\Property(property="set_on_create", type="boolean", example=false),
 *     @OA\Property(property="set_on_update", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2021-01-01 00:00:00", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2021-01-01 00:00:00", nullable=true),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", example="2021-01-01 00:00:00", nullable=true),
 * )
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string      $id
 * @property string      $template_id
 * @property string      $type
 * @property bool        $required
 * @property bool        $secret
 * @property string      $label
 * @property string      $key
 * @property string|null $value
 * @property float|null  $amount
 * @property float|null  $min
 * @property float|null  $max
 * @property float|null  $step
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon      $deleted_at
 */
class TemplateField extends Model
{
    use SoftDeletes;
    use HasUuids;
    use HasFactory;
    use LogsActivity;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'template_fields';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'required'      => 'boolean',
        'secret'        => 'boolean',
        'set_on_create' => 'boolean',
        'set_on_update' => 'boolean',
        'advanced'      => 'boolean',
    ];

    /**
     * Relation to template.
     *
     * @return HasOne
     */
    public function template(): HasOne
    {
        return $this->hasOne(Template::class, 'id', 'template_id');
    }

    /**
     * Relation to template field options.
     *
     * @return HasMany
     */
    public function options(): HasMany
    {
        return $this->hasMany(TemplateFieldOption::class, 'template_field_id', 'id');
    }
}
