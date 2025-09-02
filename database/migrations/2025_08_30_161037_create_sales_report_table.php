<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_report', function (Blueprint $table) {
            $table->id();
            $table->string('gstin')->nullable();
            $table->string('order_id')->nullable();
            $table->string('order_item_id')->nullable();
            $table->text('product_title_desc')->nullable();
            $table->string('fsn')->nullable();
            $table->string('sku')->nullable();
            $table->string('hsn_code')->nullable();
            $table->string('event_type')->nullable();
            $table->string('event_sub_type')->nullable();
            $table->string('order_type')->nullable();
            $table->string('fulfilment_type')->nullable();
            $table->dateTime('order_date')->nullable();
            $table->dateTime('order_approval_date')->nullable();
            $table->integer('item_quantity')->nullable();
            $table->string('order_shipped_from')->nullable();
            $table->string('warehouse_id')->nullable();
            $table->decimal('price_before_discount', 15, 2)->nullable();
            $table->decimal('total_discount', 15, 2)->nullable();
            $table->decimal('seller_share', 15, 2)->nullable();
            $table->decimal('bank_offer_share', 15, 2)->nullable();
            $table->decimal('price_after_discount', 15, 2)->nullable();
            $table->decimal('shipping_charges', 15, 2)->nullable();
            $table->decimal('final_invoice_amount', 15, 2)->nullable();
            $table->string('tax_type')->nullable();
            $table->decimal('taxable_value', 15, 2)->nullable();
            $table->decimal('cst_rate', 8, 2)->nullable();
            $table->decimal('cst_amount', 15, 2)->nullable();
            $table->decimal('vat_rate', 8, 2)->nullable();
            $table->decimal('vat_amount', 15, 2)->nullable();
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
            $table->string('buyer_invoice_id')->nullable();
            $table->dateTime('buyer_invoice_date')->nullable();
            $table->decimal('buyer_invoice_amount', 15, 2)->nullable();
            $table->string('buyer_pincode')->nullable();
            $table->string('buyer_state')->nullable();
            $table->string('buyer_delivery_pincode')->nullable();
            $table->string('buyer_delivery_state')->nullable();
            $table->decimal('usual_price', 15, 2)->nullable();
            $table->boolean('is_shopsy_order')->nullable();
            $table->decimal('tds_rate', 8, 2)->nullable();
            $table->decimal('tds_amount', 15, 2)->nullable();
            $table->string('irn')->nullable();
            $table->string('bussiness_name')->nullable();
            $table->string('bussiness_gst_no')->nullable();
            $table->string('beneficary_name')->nullable();
            $table->string('imei')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_report');
    }
};