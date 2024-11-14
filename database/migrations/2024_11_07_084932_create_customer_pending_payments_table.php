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
        Schema::create('customer_pending_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade'); // Foreign key to 'customers' table
            $table->foreignId('bill_id')->nullable()->constrained('billings')->onDelete('set null'); // Foreign key to 'billings' table, nullable
            $table->tinyInteger('is_cancelled')->default(0); // Cancellation status
            $table->integer('parent_id')->nullable(); // Parent ID
            $table->integer('child_id')->nullable(); // Child ID
            $table->string('current_due', 100)->nullable(); // Current due amount
            $table->integer('amount_before_gst')->nullable(); // Amount before GST
            $table->string('over_paid', 100)->nullable(); // Overpaid amount
            $table->timestamp('validity_from')->nullable(); // Validity start date
            $table->timestamp('validity_to')->nullable(); // Validity end date
            $table->integer('validity')->nullable(); // Validity period
            $table->integer('gst_id')->nullable(); // GST ID
            $table->integer('expiry_status')->nullable(); // Expiry status
            $table->timestamps(); // Adds 'created_at' and 'updated_at'
            $table->string('deducted_over_paid', 100)->default('0'); // Deducted overpaid amount
            $table->integer('is_billed')->nullable(); // Billing status
            $table->integer('removed')->nullable(); // Removed status
            $table->tinyInteger('is_membership')->default(0); // Membership flag
            $table->foreignId('membership_id')->nullable()->constrained('memberships')->onDelete('set null'); // Foreign key to 'memberships' table
            $table->tinyInteger('is_cron')->default(0); // Cron flag
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_pending_payments');
    }
};
