<?php

declare(strict_types=1);

namespace App\Models\AI;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class AiChatMessage.
 *
 * This class is the model for AI chat messages.
 *
 * @OA\Schema(
 *     schema="AiChatMessage",
 *     type="object",
 *
 *     @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="ai_chat_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="role", type="string", example="user"),
 *     @OA\Property(property="content", type="string", example="Hello, how are you?"),
 * )
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $ai_chat_id
 * @property string $role
 * @property string $content
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class AiChatMessage extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ai_chat_messages';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Relation to chat.
     *
     * @return HasOne
     */
    public function chat(): HasOne
    {
        return $this->hasOne(AiChat::class, 'id', 'ai_chat_id');
    }
}
