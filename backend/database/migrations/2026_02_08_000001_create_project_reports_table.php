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
        Schema::create('project_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects');
            $table->foreignId('submitted_by')->constrained('users'); // The SDD
            $table->date('reporting_period_start'); // Monday
            $table->date('reporting_period_end');   // Sunday
            $table->string('status')->default('draft'); // draft, submitted, amended
            $table->text('comments')->nullable();

            // Audit fields
            $table->timestamps(); // created_at, updated_at
            // No deleted_at - reports are immutable

            // Unique constraint to prevent duplicate reports for same period/project
            $table->unique(['project_id', 'reporting_period_start']);

            $table->index(['project_id', 'reporting_period_start']);
            $table->index('submitted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_reports');
    }
};
