<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     * Only created_at, no updated_at (immutable).
     *
     * @var bool
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'action',
        'user_id',
        'user_role',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable model (polymorphic).
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Prevent updates to audit logs (Constitution Principle II).
     *
     * @return bool
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        throw new \Exception('Audit logs are immutable and cannot be updated.');
    }

    /**
     * Prevent deletion of audit logs (Constitution Principle II).
     *
     * @return bool|null
     */
    public function delete(): bool|null
    {
        throw new \Exception('Audit logs are immutable and cannot be deleted.');
    }

    /**
     * Create a new audit log entry.
     *
     * @param array $attributes
     * @return static
     */
    public static function log(array $attributes): static
    {
        $defaults = [
            'user_id' => auth()->id(),
            'user_role' => auth()->user()?->getRoleNames()->first() ?? 'system',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        return static::create(array_merge($defaults, $attributes));
    }
}
