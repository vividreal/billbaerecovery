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
        Schema::create('billing_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade'); // Foreign key to 'shops' table
            $table->foreignId('bill_id')->nullable()->constrained('billings')->onDelete('set null'); // Foreign key to 'billings' table, nullable
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade'); // Foreign key to 'customers' table
            $table->string('billing_name', 255)->nullable();
            $table->foreignId('country_id')->nullable()->constrained('shop_countries')->onDelete('set null'); // Foreign key to 'countries' table, nullable
            $table->foreignId('state_id')->nullable()->constrained('shop_states')->onDelete('set null'); // Foreign key to 'states' table, nullable
            $table->foreignId('district_id')->nullable()->constrained('shop_districts')->onDelete('set null'); // Foreign key to 'districts' table, nullable
            $table->string('pincode', 255)->nullable();
            $table->string('gst', 255)->nullable();
            $table->text('address')->nullable();
            $table->integer('updated_by')->nullable(); // Nullable field for who updated the address
            $table->tinyInteger('status')->default(1); // Default status = 1
            $table->timestamps(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_addresses');
    }
};
