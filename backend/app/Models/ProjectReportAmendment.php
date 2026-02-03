<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectReportAmendment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_report_id',
        'user_id',
        'reason',
        'old_data',
        'new_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false; // Only created_at

    /**
     * Get the report that owns the amendment.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(ProjectReport::class, 'project_report_id');
    }

    /**
     * Get the user who made the amendment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
