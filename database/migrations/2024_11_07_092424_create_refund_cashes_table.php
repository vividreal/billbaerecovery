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
        Schema::create('refund_cashes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');  // Ensure 'customer_id' is unsigned
            $table->unsignedBigInteger('bill_id');  // Ensure 'bill_id' is unsigned
            $table->string('billing_code', 100)->nullable();
            $table->unsignedBigInteger('item_id')->nullable();  // Ensure 'item_id' is unsigned
            $table->unsignedBigInteger('package_id')->nullable();  // Ensure 'package_id' is unsigned
            $table->integer('payment_type')->nullable();
            $table->text('actual_amount')->nullable();
            $table->text('amount')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps(0);  // Adds 'created_at' and 'updated_at'

            // Foreign key constraints
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('bill_id')->references('id')->on('billings')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('services')->onDelete('set null');
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_cashes');
    }
};
