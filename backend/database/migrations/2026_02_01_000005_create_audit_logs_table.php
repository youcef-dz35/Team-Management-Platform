<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type', 255);
            $table->unsignedBigInteger('auditable_id');
            $table->string('action', 50); // created, updated, amended, accessed
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('user_role', 50);
            $table->jsonb('old_values')->nullable();
            $table->jsonb('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            // NOTE: No updated_at or deleted_at - this table is immutable

            $table->index(['auditable_type', 'auditable_id']);
            $table->index('user_id');
            $table->index('created_at');
        });

        // Database-level protection: Prevent updates and deletes
        // This will be applied after the table is created
        DB::statement('
            CREATE OR REPLACE RULE audit_logs_no_update AS 
            ON UPDATE TO audit_logs 
            DO INSTEAD NOTHING
        ');

        DB::statement('
            CREATE OR REPLACE RULE audit_logs_no_delete AS 
            ON DELETE TO audit_logs 
            DO INSTEAD NOTHING
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP RULE IF EXISTS audit_logs_no_update ON audit_logs');
        DB::statement('DROP RULE IF EXISTS audit_logs_no_delete ON audit_logs');
        Schema::dropIfExists('audit_logs');
    }
};
