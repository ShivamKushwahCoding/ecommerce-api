<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashback_report', function (Blueprint $table) {
            $table->id();
            $table->string('gstin')->nullable();
            $table->string('order_id')->nullable();
            $table->string('order_item_id')->nullable();
            $table->string('doc_type')->nullable();
            $table->string('doc_sub_type')->nullable();
            $table->string('credit_debit_note_id')->nullable();
            $table->decimal('invoice_amount', 15, 2)->nullable();
            $table->dateTime('invoice_date')->nullable();
            $table->decimal('taxable_value', 15, 2)->nullable();
            $table->decimal('luxury_cess_rate', 8, 2)->nullable();
            $table->decimal('luxury_cess_amount', 15, 2)->nullable();
            $table->decimal('igst_rate', 8, 2)->nullable();
            $table->decimal('igst_amount', 15, 2)->nullable();
            $table->decimal('cgst_rate', 8, 2)->nullable();
            $table->decimal('cgst_amount', 15, 2)->nullable();
            $table->decimal('sgst_rate', 8, 2)->nullable();
            $table->decimal('sgst_amount', 15, 2)->nullable();
            $table->decimal('tcs_igst_rate', 8, 2)->nullable();
            $table->decimal('tcs_igst_amount', 15, 2)->nullable();
            $table->decimal('tcs_cgst_rate', 8, 2)->nullable();
            $table->decimal('tcs_cgst_amount', 15, 2)->nullable();
            $table->decimal('tcs_sgst_rate', 8, 2)->nullable();
            $table->decimal('tcs_sgst_amount', 15, 2)->nullable();
            $table->decimal('total_tcs_deducted', 15, 2)->nullable();
            $table->string('buyer_delivery_state')->nullable();
            $table->boolean('is_shopsy_order')->nullable();
            $table->decimal('tds_rate', 8, 2)->nullable();
            $table->decimal('tds_amount', 15, 2)->nullable();
            $table->string('irn')->nullable();
            $table->string('bussiness_name')->nullable();
            $table->string('bussiness_gst_no')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashback_report');
    }
};