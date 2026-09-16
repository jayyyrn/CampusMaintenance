<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id('equipment_id');
            $table->string('asset_no', 50)->unique();
            $table->string('equipment_name', 100);
            $table->string('category', 50)->nullable();
            $table->string('location', 100)->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->enum('status', ['operational','under_repair','condemned'])->default('operational');
            $table->timestamps();

            $table->foreign('department_id')->references('dept_id')->on('departments')->onDelete('set null');
        });
    }
    public function down(): void { Schema::dropIfExists('equipment'); }
};