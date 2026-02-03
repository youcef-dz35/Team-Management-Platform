<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conflict_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
            $table->date('reporting_period_start');
            $table->date('reporting_period_end');
            $table->decimal('source_a_hours', 5, 2); // Sum from project reports
            $table->decimal('source_b_hours', 5, 2); // From department report
            $table->decimal('discrepancy', 5, 2);    // source_a - source_b
            $table->enum('status', ['open', 'resolved', 'escalated'])->default('open');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamps();

            // Unique constraint to prevent duplicate alerts for same employee/period
            $table->unique(['employee_id', 'reporting_period_start', 'reporting_period_end'], 'unique_employee_period');

            // Indexes for common queries
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conflict_alerts');
    }
};
