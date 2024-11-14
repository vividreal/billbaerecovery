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
        Schema::create('bill_amounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->nullable()->constrained('billings')->onDelete('cascade'); // Foreign key to 'billings' table
            $table->integer('parent_bill_id')->nullable(); // Nullable parent_bill_id for any hierarchical relationships
            $table->tinyInteger('billing_format_id')->nullable(); // Billing format identifier
            $table->foreignId('payment_type_id')->nullable()->constrained('payment_types')->onDelete('set null'); // Foreign key to 'payment_types' table, nullable
            $table->string('payment_type', 100)->nullable(); // Payment type description
            $table->string('amount', 100)->nullable(); // Amount
            $table->timestamps(0); // Adds 'created_at' and 'updated_at'
            $table->softDeletes(); // Adds 'deleted_at' for soft deletion
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_amounts');
    }
};
