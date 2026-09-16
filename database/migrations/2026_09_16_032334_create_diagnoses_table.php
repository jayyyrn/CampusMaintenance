<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id('diagnosis_id');
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('technician_id');
            $table->text('findings');
            $table->text('recommended_action')->nullable();
            $table->text('materials_needed')->nullable();
            $table->text('diagnosis_result')->nullable();
            $table->text('solution_steps')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->foreign('request_id')->references('request_id')->on('maintenance_requests')->onDelete('cascade');
            $table->foreign('technician_id')->references('user_id')->on('users');
            $table->foreign('verified_by')->references('user_id')->on('users')->onDelete('set null');
        });
    }
    public function down(): void { Schema::dropIfExists('diagnoses'); }
};