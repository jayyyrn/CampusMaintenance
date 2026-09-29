<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop the old email-keyed table
        Schema::dropIfExists('password_reset_tokens');

        // Create the new user_id-keyed table
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->string('token');         // hashed 6-digit code
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');

        // Restore original Breeze-style table
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }
};