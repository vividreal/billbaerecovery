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
        Schema::create('customer_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade'); // Foreign key to 'customers' table
            $table->foreignId('membership_id')->constrained('memberships')->onDelete('cascade'); // Foreign key to 'memberships' table
            $table->foreignId('bill_id')->nullable()->constrained('billings')->onDelete('set null'); // Foreign key to 'billings' table, nullable
            $table->timestamp('start_date')->nullable(); // Membership start date
            $table->timestamp('end_date')->nullable(); // Membership end date
            $table->tinyInteger('expiry_status'); // Expiry status
            $table->timestamps(); // Adds 'created_at' and 'updated_at'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_memberships');
    }
};
