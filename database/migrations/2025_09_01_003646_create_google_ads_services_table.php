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
        Schema::create('google_ads_services', function (Blueprint $table) {
            $table->id();
            $table->string('neft_id')->nullable();
            $table->date('payment_date')->nullable();
            $table->decimal('settlement_value', 15, 2)->nullable();
            $table->string('service_name')->nullable();
            $table->string('service_details')->nullable();
            $table->string('service_order_id')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->decimal('service_amount', 15, 2)->nullable();
            $table->decimal('gst_on_service_amount', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_ads_services');
    }
};
