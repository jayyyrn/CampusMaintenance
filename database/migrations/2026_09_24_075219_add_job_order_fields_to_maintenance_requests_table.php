<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->string('unit_no', 50)->nullable()->after('request_code');
            $table->text('tools_and_materials')->nullable()->after('description');
            $table->decimal('estimated_budget', 10, 2)->nullable()->after('tools_and_materials');
            $table->string('date_start', 50)->nullable()->after('date_reported');
            $table->string('date_finish', 50)->nullable()->after('date_start');
        });
    }

    public function down(): void {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropColumn(['unit_no','tools_and_materials','estimated_budget','date_start','date_finish']);
        });
    }
};