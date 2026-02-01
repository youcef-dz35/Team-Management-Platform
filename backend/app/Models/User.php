<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'department_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the department this user belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get projects managed by this user (if SDD).
     */
    public function managedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'sdd_id');
    }

    /**
     * Get projects assigned to this user (as worker).
     */
    public function assignedProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_assignments')
            ->withPivot('allocated_hours')
            ->withTimestamps();
    }

    /**
     * Get project IDs assigned to this user.
     *
     * @return \Illuminate\Support\Collection
     */
    public function assignedProjectIds()
    {
        return $this->assignedProjects()->pluck('projects.id');
    }

    /**
     * Check if user has god-mode access (CEO or CFO).
     */
    public function hasGodMode(): bool
    {
        return $this->hasAnyRole(['ceo', 'cfo']);
    }

    /**
     * Check if user is an SDD.
     */
    public function isSdd(): bool
    {
        return $this->hasRole('sdd');
    }

    /**
     * Check if user is a Department Manager.
     */
    public function isDepartmentManager(): bool
    {
        return $this->hasRole('dept_manager');
    }

    /**
     * Get audit logs for this user's actions.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
