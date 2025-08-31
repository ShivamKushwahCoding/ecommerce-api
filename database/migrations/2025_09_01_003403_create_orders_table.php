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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('neft_id')->nullable();
            $table->string('neft_type')->nullable();
            $table->date('payment_date')->nullable();
            $table->decimal('bank_settlement_value', 15, 2)->nullable();
            $table->decimal('input_gst_tcs_credits', 15, 2)->nullable();
            $table->decimal('income_tax_credits', 15, 2)->nullable();
            $table->string('order_id')->nullable();
            $table->string('order_item_id')->nullable();
            $table->decimal('sale_amount', 15, 2)->nullable();
            $table->decimal('total_offer_amount', 15, 2)->nullable();
            $table->decimal('my_share', 15, 2)->nullable();
            $table->decimal('customer_addons_amount', 15, 2)->nullable();
            $table->decimal('marketplace_fee', 15, 2)->nullable();
            $table->decimal('taxes', 15, 2)->nullable();
            $table->decimal('offer_adjustments', 15, 2)->nullable();
            $table->decimal('protection_fund', 15, 2)->nullable();
            $table->decimal('refund', 15, 2)->nullable();
            $table->string('tier')->nullable();
            $table->decimal('commission_rate', 8, 2)->nullable();
            $table->decimal('commission', 15, 2)->nullable();
            $table->decimal('fixed_fee', 15, 2)->nullable();
            $table->decimal('collection_fee', 15, 2)->nullable();
            $table->decimal('pick_pack_fee', 15, 2)->nullable();
            $table->decimal('shipping_fee', 15, 2)->nullable();
            $table->decimal('reverse_shipping_fee', 15, 2)->nullable();
            $table->decimal('nocost_emi_fee_reimbursement', 15, 2)->nullable();
            $table->decimal('installation_fee', 15, 2)->nullable();
            $table->decimal('tech_visit_fee', 15, 2)->nullable();
            $table->decimal('uninstallation_packaging_fee', 15, 2)->nullable();
            $table->decimal('customer_addons_amount_recovery', 15, 2)->nullable();
            $table->decimal('franchise_fee', 15, 2)->nullable();
            $table->decimal('shopsy_marketing_fee', 15, 2)->nullable();
            $table->decimal('product_cancellation_fee', 15, 2)->nullable();
            $table->decimal('tcs', 15, 2)->nullable();
            $table->decimal('tds', 15, 2)->nullable();
            $table->decimal('gst_mp_fees', 15, 2)->nullable();
            $table->decimal('offer_amount_discount_mp_fee', 15, 2)->nullable();
            $table->decimal('item_gst_rate', 8, 2)->nullable();
            $table->decimal('discount_mp_fee', 15, 2)->nullable();
            $table->decimal('gst_discount', 15, 2)->nullable();
            $table->decimal('total_discount_mp_fee', 15, 2)->nullable();
            $table->decimal('offer_adjustment', 15, 2)->nullable();
            $table->decimal('dead_weight', 15, 3)->nullable();
            $table->string('length*breadth*height')->nullable();
            $table->decimal('volumetric_weight', 15, 3)->nullable();
            $table->string('chargeable_weight_source')->nullable();
            $table->string('chargeable_weight_type')->nullable();
            $table->decimal('chargeable_weight_slab', 15, 3)->nullable();
            $table->string('shipping_zone')->nullable();
            $table->date('order_date')->nullable();
            $table->date('dispatch_date')->nullable();
            $table->string('fulfilment_type')->nullable();
            $table->string('seller_sku')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('product_sub_category')->nullable();
            $table->text('additional_info')->nullable();
            $table->string('return_type')->nullable();
            $table->string('shopsy_order')->nullable();
            $table->string('item_return_status')->nullable();
            $table->string('invoice_id')->nullable();
            $table->date('invoice_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
