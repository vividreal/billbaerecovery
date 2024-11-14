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
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
            $table->string('billing_code', 100)->nullable();
            $table->integer('parent_id')->nullable();  // Nullable parent_id for any hierarchical relationships.
            $table->foreignId('shop_id')->constrained('shops')->nullable()->onDelete('cascade');  // Foreign key to 'shops' table.
            $table->unsignedBigInteger('customer_id')->nullable();  // Ensure it's unsigned to match 'customers.id'
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null'); // Foreign key constraint
            $table->longText('customer_address')->nullable();
            $table->tinyInteger('customer_type')->nullable()->comment('1 - New, 0 - Existing');
            $table->tinyText('payment_status')->nullable()->comment('0 = pending, 1 = completed, 2 = cancelled_bill, 3 = due_payment, 4 = additional_payment');
            $table->string('payment_method', 100)->nullable();
            $table->string('address_type', 100)->nullable();
            $table->string('amount', 255)->nullable();
            $table->string('actual_amount', 255)->nullable();
            $table->dateTime('billed_date')->nullable();
            $table->date('paid_date')->nullable();
            $table->string('checkin_time', 100)->nullable();
            $table->string('checkout_time', 100)->nullable();
            $table->tinyInteger('status')->default(0)->comment('0 - Open, 1 - Closed, 2 - Cancelled');
            $table->timestamps(0);  // Adds 'created_at', 'updated_at'.
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};
