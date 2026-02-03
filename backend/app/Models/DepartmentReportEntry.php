<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentReportEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_report_id',
        'user_id',
        'project_id',
        'hours_allocated',
        'notes',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(DepartmentReport::class, 'department_report_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class); // The employee
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
