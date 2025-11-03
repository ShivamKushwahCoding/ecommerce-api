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
        Schema::create('return_reports', function (Blueprint $table) {
            $table->id();
            $table->string('return_id')->nullable();
            $table->string('order_item_id')->nullable();
            $table->string('fulfilment_type')->nullable();
            $table->string('return_requested_date')->nullable();
            $table->string('return_approval_date')->nullable();
            $table->string('return_status')->nullable();
            $table->string('return_reason')->nullable();
            $table->string('return_sub_reason')->nullable();
            $table->string('return_type')->nullable();
            $table->string('return_result')->nullable();
            $table->string('return_expectation')->nullable();
            $table->string('reverse_logistics_tracking_id')->nullable();
            $table->string('sku')->nullable();
            $table->string('fsn')->nullable();
            $table->string('product_title')->nullable();
            $table->string('quantity')->nullable();
            $table->string('return_completion_type')->nullable();
            $table->string('primary_pv_output')->nullable();
            $table->string('detailed_pv_output')->nullable();
            $table->string('final_condition_of_returned_product')->nullable();
            $table->string('tech_visit_sla')->nullable();
            $table->string('tech_visit_by_date')->nullable();
            $table->string('tech_visit_completion_datetime')->nullable();
            $table->string('tech_visit_completion_breach')->nullable();
            $table->string('return_completion_sla')->nullable();
            $table->string('return_complete_by_date')->nullable();
            $table->string('return_completion_date')->nullable();
            $table->string('return_completion_breach')->nullable();
            $table->string('return_cancellation_date')->nullable();
            $table->string('return_cancellation_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_reports');
    }
};
