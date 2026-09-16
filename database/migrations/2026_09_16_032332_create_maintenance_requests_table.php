<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id('request_id');
            $table->string('request_code', 30)->unique();
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('equipment_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->enum('category', ['electrical','carpentry','fabrication','aircon','plumbing','general'])->default('general');
            $table->string('title', 200);
            $table->text('description');
            $table->string('location', 100)->nullable();
            $table->enum('priority', ['low','medium','high','urgent'])->default('medium');
            $table->enum('status', ['pending','review','assigned','in_progress','for_verification','completed','cancelled'])->default('pending');
            $table->integer('queue_position')->default(0);
            $table->string('photo_before')->nullable();
            $table->string('photo_after')->nullable();
            $table->timestamp('date_reported')->useCurrent();
            $table->timestamp('date_completed')->nullable();
            $table->timestamps();

            $table->foreign('teacher_id')->references('user_id')->on('users');
            $table->foreign('equipment_id')->references('equipment_id')->on('equipment')->onDelete('set null');
            $table->foreign('department_id')->references('dept_id')->on('departments')->onDelete('set null');
        });
    }
    public function down(): void { Schema::dropIfExists('maintenance_requests'); }
};