<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id('transaction_id');
            $table->unsignedBigInteger('item_id');
            $table->enum('transaction_type', ['stock_in','stock_out','return','adjustment']);
            $table->integer('quantity');
            $table->string('reference_no', 50)->nullable();
            $table->unsignedBigInteger('request_id')->nullable();
            $table->unsignedBigInteger('performed_by');
            $table->string('supplier', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('item_id')->references('item_id')->on('inventory');
            $table->foreign('request_id')->references('request_id')->on('maintenance_requests')->onDelete('set null');
            $table->foreign('performed_by')->references('user_id')->on('users');
        });
    }
    public function down(): void { Schema::dropIfExists('stock_transactions'); }
};