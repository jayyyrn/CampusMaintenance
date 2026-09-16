<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory', function (Blueprint $table) {
            $table->id('item_id');
            $table->string('item_code', 20)->unique();
            $table->string('item_name', 100);
            $table->string('category', 50)->nullable();
            $table->string('unit', 20)->default('pcs');
            $table->integer('qty_on_hand')->default(0);
            $table->integer('min_stock_level')->default(5);
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->string('location', 100)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('inventory'); }
};