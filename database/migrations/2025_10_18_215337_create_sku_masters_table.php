<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sku_masters', function (Blueprint $table) {
            $table->id();
            $table->string('channel')->default('flipkart')->nullable();
            $table->string('product_Name')->nullable();
            $table->string('sku')->nullable();
            $table->string('product_id')->nullable();
            $table->string('cost_price_with_gst')->nullable();
            $table->string('gst')->nullable();
            $table->string('cost_price_without_gst')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sku_masters');
    }
};
