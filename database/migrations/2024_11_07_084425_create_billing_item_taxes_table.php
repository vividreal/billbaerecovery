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
        Schema::create('billing_item_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->nullable()->constrained('billings')->onDelete('cascade'); // Foreign key to 'billings' table
            $table->foreignId('bill_item_id')->nullable()->constrained('billing_items')->onDelete('cascade'); // Foreign key to 'billing_items' table
            $table->foreignId('item_id')->nullable()->constrained('services')->onDelete('set null'); // Foreign key to 'services' table as item_id
            $table->string('tax_method', 255)->nullable(); // Tax method
            $table->string('total_tax_percentage', 200)->nullable(); // Total tax percentage
            $table->string('cgst_percentage', 100)->nullable(); // CGST percentage
            $table->string('sgst_percentage', 100)->nullable(); // SGST percentage
            $table->string('cgst_amount', 100)->nullable(); // CGST amount
            $table->string('sgst_amount', 100)->nullable(); // SGST amount
            $table->string('grand_total', 100)->nullable(); // Grand total
            $table->string('tax_amount', 255)->nullable(); // Tax amount
            $table->timestamps(0); // Adds 'created_at' and 'updated_at'
            $table->softDeletes(); // Adds 'deleted_at' for soft deletion
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_item_taxes');
    }
};
