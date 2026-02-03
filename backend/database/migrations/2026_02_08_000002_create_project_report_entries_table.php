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
        Schema::create('project_report_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_report_id')->constrained('project_reports')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users'); // The Employee/Worker
            $table->decimal('hours_worked', 5, 2); // Max 168.00
            $table->text('notes')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index('project_report_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_report_entries');
    }
};
