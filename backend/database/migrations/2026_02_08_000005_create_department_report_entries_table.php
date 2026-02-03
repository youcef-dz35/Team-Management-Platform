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
        Schema::create('department_report_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_report_id')->constrained('department_reports')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('users'); // Employee being reported on
            $table->decimal('hours_worked', 5, 2)->check('hours_worked >= 0'); // Max 168.00
            $table->integer('tasks_completed')->default(0);
            $table->string('status')->nullable(); // productive, underperforming, on_leave
            $table->text('work_description')->nullable();

            $table->timestamps();

            $table->unique(['department_report_id', 'employee_id']);
            $table->index('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_report_entries');
    }
};
