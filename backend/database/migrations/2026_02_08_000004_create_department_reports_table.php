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
        Schema::create('department_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained(); // The manager who created the report

            $table->date('period_start'); // Monday
            $table->date('period_end');   // Sunday

            $table->enum('status', ['draft', 'submitted', 'amended'])->default('draft');
            $table->text('comments')->nullable();

            // Immutability: No updated_at provided for business logic updates, 
            // but Laravel expects timestamps() often. We can use just created_at or both.
            // Using timestamps() for convenience, but policy will enforce immutability.
            $table->timestamps();

            // Ensure one report per department per week
            $table->unique(['department_id', 'period_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_reports');
    }
};
