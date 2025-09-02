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
        Schema::create('storage_recall', function (Blueprint $table) {
            $table->id();
            $table->string('neft_id')->nullable();
            $table->date('payment_date')->nullable();
            $table->decimal('settlement_value', 15, 2)->nullable();
            $table->string('service_name')->nullable();
            $table->string('listing_id')->nullable();
            $table->string('recall_id')->nullable();
            $table->string('warehouse_state_code')->nullable();
            $table->string('fsn')->nullable();
            $table->decimal('marketplacefees', 15, 2)->nullable();
            $table->decimal('gst_on_storage_recall_fees', 15, 2)->nullable();
            $table->integer('removal_fee_units')->nullable();
            $table->decimal('removal_fee', 15, 2)->nullable();
            $table->integer('storage_fee_units')->nullable();
            $table->decimal('storage_fee_upto_jun_30_2016', 15, 2)->nullable();
            $table->integer('sellable_regular_storage_units')->nullable();
            $table->decimal('sellable_regular_storage', 15, 2)->nullable();
            $table->integer('unsellable_regular_storage_units')->nullable();
            $table->decimal('unsellable_regular_storage', 15, 2)->nullable();
            $table->string('product_sub_category')->nullable();
            $table->decimal('dead_weight', 15, 3)->nullable();
            $table->string('length*breadth*height')->nullable();
            $table->decimal('volumetric_weight', 15, 3)->nullable();
            $table->decimal('chargeable_weight_slab', 15, 3)->nullable();
            $table->string('chargeable_weight_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_recall');
    }
};
