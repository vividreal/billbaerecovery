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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade'); // Foreign key to 'shops' table
            $table->string('name', 255)->nullable(); // Schedule name
            $table->longText('description')->nullable(); // Schedule description
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Foreign key to 'users' table, nullable
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null'); // Foreign key to 'customers' table, nullable
            $table->foreignId('billing_id')->nullable()->constrained('billings')->onDelete('set null'); // Foreign key to 'billings' table, nullable
            $table->foreignId('item_id')->nullable()->constrained('services')->onDelete('set null'); // Foreign key to 'items' table, nullable
            $table->foreignId('package_id')->nullable()->constrained('packages')->onDelete('set null'); // Foreign key to 'packages' table, nullable
            $table->string('item_type', 255)->nullable(); // Item type
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null'); // Foreign key to 'rooms' table, nullable
            $table->dateTime('start')->nullable(); // Start date and time
            $table->dateTime('end')->nullable(); // End date and time
            $table->tinyInteger('checked_in')->default(0); // Check-in status
            $table->string('total_minutes', 100)->nullable(); // Total minutes
            $table->tinyInteger('payment_status')->default(0); // Payment status (0 - pending, 1 - paid)
            $table->string('schedule_color', 255)->nullable(); // Color for the schedule
            $table->tinyInteger('status')->default(0); // Schedule status (0 - active, 1 - cancelled, etc.)
            $table->timestamps(0); // Adds 'created_at' and 'updated_at'
            $table->softDeletes(); // Adds 'deleted_at' for soft deletes
     
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
