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
        Schema::create('mp_fee_rebate', function (Blueprint $table) {
            $table->id();
            $table->string('neft_id')->nullable();
            $table->string('neft_type')->nullable();
            $table->date('payment_date')->nullable();
            $table->decimal('settlement_value', 15, 2)->nullable();
            $table->string('sku')->nullable();
            $table->string('order_id')->nullable();
            $table->string('order_item_id')->nullable();
            $table->date('order_date')->nullable();
            $table->date('rebate_processing_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_fee_rebate');
    }
};
