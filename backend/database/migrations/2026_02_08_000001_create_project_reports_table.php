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
            $table->foreignId('user_id')->constrained('users'); // The SDD
            $table->date('period_start'); // Monday
            $table->date('period_end');   // Sunday
            $table->string('status')->default('draft'); // draft, submitted, amended
            $table->text('comments')->nullable();

            // Audit fields
            $table->timestamp('created_at')->useCurrent();
            // No updated_at - use amendments table for changes
            // No deleted_at - reports are immutable

            // Unique constraint to prevent duplicate reports for same period/project
            $table->unique(['project_id', 'period_start']);

            $table->index(['project_id', 'period_start']);
            $table->index('user_id');
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
