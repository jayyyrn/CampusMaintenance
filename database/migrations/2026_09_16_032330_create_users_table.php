<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('username', 50)->unique();
            $table->string('full_name', 100);
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->enum('role', ['teacher','coordinator','technician','lead_technician','inventory_officer','admin']);
            $table->unsignedBigInteger('department_id')->nullable();
            $table->enum('status', ['active','inactive'])->default('active');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('department_id')->references('dept_id')->on('departments')->onDelete('set null');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }
    public function down(): void { Schema::dropIfExists('sessions'); Schema::dropIfExists('users'); }
};