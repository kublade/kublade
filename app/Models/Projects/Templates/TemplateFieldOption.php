<?php

declare(strict_types=1);

namespace App\Models\Projects\Templates;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TemplateFieldOption.
 *
 * This class is the model for template field options.
 *
 * @OA\Schema(
 *     schema="TemplateFieldOption",
 *     type="object",
 *
 *     @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="template_field_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="label", type="string", example="Label"),
 *     @OA\Property(property="value", type="string", example="Value"),
 *     @OA\Property(property="default", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2021-01-01 00:00:00", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2021-01-01 00:00:00", nullable=true),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", example="2021-01-01 00:00:00", nullable=true),
 * )
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string     $id
 * @property string     $template_field_id
 * @property string     $label
 * @property string     $value
 * @property float|null $amount
 * @property bool       $default
 * @property Carbon     $created_at
 * @property Carbon     $updated_at
 * @property Carbon     $deleted_at
 */
class TemplateFieldOption extends Model
{
    use SoftDeletes;
    use HasUuids;
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'template_field_options';

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
        'default' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (self $option) {
            if ($option->default) {
                // Remove default from other options of the same field
                static::where('template_field_id', $option->template_field_id)
                    ->where('id', '!=', $option->id)
                    ->update(['default' => false]);
            }
        });
    }

    /**
     * Relation to template field.
     *
     * @return HasOne
     */
    public function field(): HasOne
    {
        return $this->hasOne(TemplateField::class, 'id', 'template_field_id');
    }
}
