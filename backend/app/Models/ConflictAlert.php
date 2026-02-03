<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConflictAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'reporting_period_start',
        'reporting_period_end',
        'source_a_hours',
        'source_b_hours',
        'discrepancy',
        'status',
        'resolved_by',
        'resolution_notes',
        'resolved_at',
        'escalated_at',
    ];

    protected $casts = [
        'reporting_period_start' => 'date',
        'reporting_period_end' => 'date',
        'source_a_hours' => 'decimal:2',
        'source_b_hours' => 'decimal:2',
        'discrepancy' => 'decimal:2',
        'resolved_at' => 'datetime',
        'escalated_at' => 'datetime',
    ];

    /**
     * The employee who has the discrepancy.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * The user who resolved this conflict (CEO/CFO/GM).
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Scope for open conflicts.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope for escalated conflicts.
     */
    public function scopeEscalated($query)
    {
        return $query->where('status', 'escalated');
    }

    /**
     * Scope for unresolved conflicts (open or escalated).
     */
    public function scopeUnresolved($query)
    {
        return $query->whereIn('status', ['open', 'escalated']);
    }

    /**
     * Check if this conflict should be escalated (older than 7 days).
     */
    public function shouldEscalate(): bool
    {
        return $this->status === 'open'
            && $this->created_at->diffInDays(now()) >= 7;
    }

    /**
     * Escalate this conflict to CEO/CFO.
     */
    public function escalate(): void
    {
        $this->update([
            'status' => 'escalated',
            'escalated_at' => now(),
        ]);
    }

    /**
     * Resolve this conflict with notes.
     */
    public function resolve(User $resolver, string $notes): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_by' => $resolver->id,
            'resolution_notes' => $notes,
            'resolved_at' => now(),
        ]);
    }
}
