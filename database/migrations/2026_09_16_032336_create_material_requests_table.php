<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('material_requests', function (Blueprint $table) {
            $table->id('mat_req_id');
            $table->unsignedBigInteger('request_id')->nullable();
            $table->unsignedBigInteger('technician_id');
            $table->unsignedBigInteger('item_id');
            $table->integer('quantity_requested');
            $table->integer('quantity_released')->default(0);
            $table->integer('quantity_returned')->default(0);
            $table->enum('status', ['pending','approved','released','returned','rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('released_by')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('request_id')->references('request_id')->on('maintenance_requests')->onDelete('set null');
            $table->foreign('technician_id')->references('user_id')->on('users');
            $table->foreign('item_id')->references('item_id')->on('inventory');
            $table->foreign('approved_by')->references('user_id')->on('users')->onDelete('set null');
            $table->foreign('released_by')->references('user_id')->on('users')->onDelete('set null');
        });
    }
    public function down(): void { Schema::dropIfExists('material_requests'); }
};