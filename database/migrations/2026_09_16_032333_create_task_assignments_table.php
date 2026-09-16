<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('task_assignments', function (Blueprint $table) {
            $table->id('assignment_id');
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('technician_id');
            $table->unsignedBigInteger('assigned_by');
            $table->enum('status', ['pending','in_progress','for_review','completed'])->default('pending');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('request_id')->references('request_id')->on('maintenance_requests')->onDelete('cascade');
            $table->foreign('technician_id')->references('user_id')->on('users');
            $table->foreign('assigned_by')->references('user_id')->on('users');
        });
    }
    public function down(): void { Schema::dropIfExists('task_assignments'); }
};