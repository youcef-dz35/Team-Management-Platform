<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index('department_id', 'idx_users_department_id');
            $table->index('email', 'idx_users_email');
            $table->index('role', 'idx_users_role');
        });

        // Departments table indexes
        Schema::table('departments', function (Blueprint $table) {
            $table->index('name', 'idx_departments_name');
        });

        // Projects table indexes
        Schema::table('projects', function (Blueprint $table) {
            $table->index('name', 'idx_projects_name');
        });

        // Project reports indexes
        Schema::table('project_reports', function (Blueprint $table) {
            $table->index('project_id', 'idx_project_reports_project_id');
            $table->index('user_id', 'idx_project_reports_user_id');
            $table->index(['status', 'reporting_period_start'], 'idx_project_reports_status_period');
            $table->index('created_at', 'idx_project_reports_created_at');
        });

        // Department reports indexes
        Schema::table('department_reports', function (Blueprint $table) {
            $table->index('department_id', 'idx_department_reports_dept_id');
            $table->index('user_id', 'idx_department_reports_user_id');
            $table->index(['status', 'reporting_period_start'], 'idx_department_reports_status_period');
            $table->index('created_at', 'idx_department_reports_created_at');
        });

        // Project report entries indexes
        Schema::table('project_report_entries', function (Blueprint $table) {
            $table->index('project_report_id', 'idx_project_entries_report_id');
            $table->index('user_id', 'idx_project_entries_user_id');
            $table->index('date', 'idx_project_entries_date');
        });

        // Department report entries indexes
        Schema::table('department_report_entries', function (Blueprint $table) {
            $table->index('department_report_id', 'idx_department_entries_report_id');
            $table->index('user_id', 'idx_department_entries_user_id');
            $table->index('date', 'idx_department_entries_date');
        });

        // Conflict alerts indexes
        Schema::table('conflict_alerts', function (Blueprint $table) {
            $table->index('employee_id', 'idx_conflict_alerts_employee_id');
            $table->index('resolver_id', 'idx_conflict_alerts_resolver_id');
            $table->index(['status', 'created_at'], 'idx_conflict_alerts_status_created');
            $table->index('reporting_period', 'idx_conflict_alerts_period');
        });

        // Audit logs indexes (if exists)
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index('user_id', 'idx_audit_logs_user_id');
                $table->index('auditable_type', 'idx_audit_logs_type');
                $table->index(['auditable_type', 'auditable_id'], 'idx_audit_logs_polymorphic');
                $table->index('created_at', 'idx_audit_logs_created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop audit logs indexes
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropIndex('idx_audit_logs_user_id');
                $table->dropIndex('idx_audit_logs_type');
                $table->dropIndex('idx_audit_logs_polymorphic');
                $table->dropIndex('idx_audit_logs_created_at');
            });
        }

        // Drop conflict alerts indexes
        Schema::table('conflict_alerts', function (Blueprint $table) {
            $table->dropIndex('idx_conflict_alerts_employee_id');
            $table->dropIndex('idx_conflict_alerts_resolver_id');
            $table->dropIndex('idx_conflict_alerts_status_created');
            $table->dropIndex('idx_conflict_alerts_period');
        });

        // Drop department report entries indexes
        Schema::table('department_report_entries', function (Blueprint $table) {
            $table->dropIndex('idx_department_entries_report_id');
            $table->dropIndex('idx_department_entries_user_id');
            $table->dropIndex('idx_department_entries_date');
        });

        // Drop project report entries indexes
        Schema::table('project_report_entries', function (Blueprint $table) {
            $table->dropIndex('idx_project_entries_report_id');
            $table->dropIndex('idx_project_entries_user_id');
            $table->dropIndex('idx_project_entries_date');
        });

        // Drop department reports indexes
        Schema::table('department_reports', function (Blueprint $table) {
            $table->dropIndex('idx_department_reports_dept_id');
            $table->dropIndex('idx_department_reports_user_id');
            $table->dropIndex('idx_department_reports_status_period');
            $table->dropIndex('idx_department_reports_created_at');
        });

        // Drop project reports indexes
        Schema::table('project_reports', function (Blueprint $table) {
            $table->dropIndex('idx_project_reports_project_id');
            $table->dropIndex('idx_project_reports_user_id');
            $table->dropIndex('idx_project_reports_status_period');
            $table->dropIndex('idx_project_reports_created_at');
        });

        // Drop projects indexes
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('idx_projects_name');
        });

        // Drop departments indexes
        Schema::table('departments', function (Blueprint $table) {
            $table->dropIndex('idx_departments_name');
        });

        // Drop users indexes
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_department_id');
            $table->dropIndex('idx_users_email');
            $table->dropIndex('idx_users_role');
        });
    }
};
