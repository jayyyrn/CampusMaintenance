<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Extend ENUM to accept 'reassigned' as a valid status
        DB::statement("ALTER TABLE task_assignments MODIFY COLUMN status ENUM('pending','in_progress','for_review','completed','reassigned') NOT NULL DEFAULT 'pending'");

        // Add ended_at timestamp to track when an assignment was reassigned or completed
        Schema::table('task_assignments', function (Blueprint $table) {
            $table->timestamp('ended_at')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        // Remove ended_at
        Schema::table('task_assignments', function (Blueprint $table) {
            $table->dropColumn('ended_at');
        });

        // Convert 'reassigned' rows back to 'pending' before shrinking the ENUM
        DB::statement("UPDATE task_assignments SET status = 'pending' WHERE status = 'reassigned'");

        // Revert ENUM to original values
        DB::statement("ALTER TABLE task_assignments MODIFY COLUMN status ENUM('pending','in_progress','for_review','completed') NOT NULL DEFAULT 'pending'");
    }
};