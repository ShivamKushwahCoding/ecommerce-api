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
        Schema::create('non_order_spf', function (Blueprint $table) {
            $table->id();
            $table->string('neft_id')->nullable();
            $table->date('payment_date')->nullable();
            $table->decimal('settlement_value', 15, 2)->nullable();
            $table->string('claim_id')->nullable();
            $table->string('protection_reason')->nullable();
            $table->string('seller_sku')->nullable();
            $table->string('fsn')->nullable();
            $table->decimal('selling_price', 15, 2)->nullable();
            $table->string('warehouse_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('non_order_spf');
    }
};
