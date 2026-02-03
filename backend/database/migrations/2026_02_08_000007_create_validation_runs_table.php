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
        Schema::create('validation_runs', function (Blueprint $table) {
            $table->id();
            $table->date('reporting_period_start');
            $table->date('reporting_period_end');
            $table->integer('employees_checked')->default(0);
            $table->integer('conflicts_found')->default(0);
            $table->integer('run_duration_ms')->nullable();
            $table->enum('status', ['running', 'completed', 'failed'])->default('running');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validation_runs');
    }
};
