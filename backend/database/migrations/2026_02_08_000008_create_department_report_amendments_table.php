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
        Schema::create('department_report_amendments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_report_id')->constrained()->onDelete('cascade');
            $table->foreignId('amended_by')->constrained('users')->onDelete('cascade');
            $table->text('amendment_reason');
            $table->jsonb('changes'); // Before/after values
            $table->timestamp('created_at')->useCurrent();
            // No updated_at - amendments are immutable

            $table->index('department_report_id');
            $table->index('amended_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_report_amendments');
    }
};
