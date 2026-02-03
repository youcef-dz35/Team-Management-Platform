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
        Schema::create('project_report_amendments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_report_id')->constrained('project_reports')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users'); // Who made the amendment
            $table->text('reason');
            $table->jsonb('old_data'); // Snapshot before change
            $table->jsonb('new_data'); // Snapshot after change

            $table->timestamp('created_at')->useCurrent();

            $table->index('project_report_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_report_amendments');
    }
};
