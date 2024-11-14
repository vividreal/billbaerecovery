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
        Schema::create('billing_item_additional_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->nullable()->constrained('billings')->onDelete('cascade'); // Foreign key to 'billings' table
            $table->foreignId('bill_item_id')->nullable()->constrained('billing_items')->onDelete('cascade'); // Foreign key to 'billing_items' table
            $table->foreignId('item_id')->nullable()->constrained('services')->onDelete('set null'); // Foreign key to 'services' table as item_id
            $table->string('tax_name', 200)->nullable(); // Tax name
            $table->string('percentage', 100)->nullable(); // Tax percentage
            $table->string('amount', 100)->nullable(); // Tax amount
            $table->timestamps(0); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_item_additional_taxes');
    }
};
