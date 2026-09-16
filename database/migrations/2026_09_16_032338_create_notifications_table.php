<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('notification_id');
            $table->unsignedBigInteger('user_id');
            $table->string('title', 200);
            $table->text('message');
            $table->enum('type', ['info','success','warning','danger'])->default('info');
            $table->boolean('is_read')->default(false);
            $table->string('link', 255)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }
    public function down(): void { Schema::dropIfExists('notifications'); }
};