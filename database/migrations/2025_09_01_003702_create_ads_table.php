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
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('neft_id')->nullable();
            $table->date('payment_date')->nullable();
            $table->decimal('settlement_value', 15, 2)->nullable();
            $table->string('type')->nullable();
            $table->string('campaign_transaction_id')->nullable();
            $table->decimal('wallet_redeem', 15, 2)->nullable();
            $table->decimal('wallet_redeem_reversal', 15, 2)->nullable();
            $table->decimal('wallet_topup', 15, 2)->nullable();
            $table->decimal('wallet_refund', 15, 2)->nullable();
            $table->decimal('gst_on_ads_fees', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
