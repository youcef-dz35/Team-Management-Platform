<?php

namespace App\Providers;

use App\Models\ConflictAlert;
use App\Models\DepartmentReport;
use App\Models\ProjectReport;
use App\Policies\ConflictAlertPolicy;
use App\Policies\DepartmentReportPolicy;
use App\Policies\ProjectReportPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers for audit logging
        \App\Models\User::observe(\App\Observers\AuditLogObserver::class);
        \App\Models\Department::observe(\App\Observers\AuditLogObserver::class);
        \App\Models\Project::observe(\App\Observers\AuditLogObserver::class);
        \App\Models\ProjectReport::observe(\App\Observers\AuditLogObserver::class);
        \App\Models\DepartmentReport::observe(\App\Observers\AuditLogObserver::class);
        \App\Models\ConflictAlert::observe(\App\Observers\AuditLogObserver::class);

        // Register policies
        Gate::policy(ProjectReport::class, ProjectReportPolicy::class);
        Gate::policy(DepartmentReport::class, DepartmentReportPolicy::class);
        Gate::policy(ConflictAlert::class, ConflictAlertPolicy::class);
    }
}
