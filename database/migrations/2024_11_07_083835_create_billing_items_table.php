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
        Schema::create('billing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_id')->constrained('billings')->onDelete('cascade'); // Foreign key to 'billings' table
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null'); // Foreign key to 'customers' table, nullable
            $table->string('item_type', 255)->nullable(); // Type of item
            $table->foreignId('item_id')->constrained('services')->onDelete('cascade'); // Foreign key to 'services' table as service_id
            $table->foreignId('package_id')->nullable()->constrained('packages')->onDelete('set null'); // Foreign key to 'packages' table, nullable
            $table->integer('item_count')->default(1); // Default item count of 1
            $table->longText('item_details')->nullable(); // Additional item details
            $table->tinyInteger('is_discount_used')->default(0); // Discount usage flag, default 0
            $table->string('discount_type', 100)->nullable(); // Discount type
            $table->string('discount_value', 100)->nullable(); // Discount value
            $table->string('discount_amount', 255)->nullable(); // Discount amount
            $table->timestamp('validity_from')->nullable(); // Validity start date
            $table->timestamp('validity_to')->nullable(); // Validity end date
            $table->integer('expiry_status')->nullable(); // Expiry status
            $table->integer('validity')->nullable(); // Validity period
            $table->timestamps(0); // Adds 'created_at' and 'updated_at'
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_items');
    }
};
