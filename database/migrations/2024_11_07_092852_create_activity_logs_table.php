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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('service_type', 255)->nullable();
            $table->integer('service_id')->nullable();
            $table->foreignId('bill_id')->nullable()->constrained('billings')->onDelete('set null');
            $table->foreignId('schedule_id')->nullable()->constrained('schedules')->onDelete('set null');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('previous_status', ['0','1','2','3','4','5','6','7','8','9','10','11','12'])->nullable();
            $table->enum('current_status', ['0','1','2','3','4','5','6','7','8','9','10','11','12'])->nullable();
            $table->string('comment', 255)->nullable();
            $table->timestamps(0);  // Adds 'created_at' and 'updated_at'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
