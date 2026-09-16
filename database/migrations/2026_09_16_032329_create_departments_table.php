<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('departments', function (Blueprint $table) {
            $table->id('dept_id');
            $table->string('dept_name', 100);
            $table->string('dept_code', 20)->unique();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('departments'); }
};