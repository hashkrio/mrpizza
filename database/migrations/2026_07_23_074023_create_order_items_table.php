<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();

            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_name');
            $table->string('size')->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('item_price', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('lang', 10)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};