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
        Schema::create('schedule_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->nullable()->constrained('schedules')->onDelete('set null'); // Foreign key to 'schedules' table
            $table->integer('parent_id')->nullable(); // Regular nullable column for parent_id (no foreign key constraint)
            $table->text('previous_color')->nullable(); // Previous color status
            $table->text('checked_in_value')->nullable(); // Checked-in status value
            $table->timestamps(0); // Adds 'created_at' and 'updated_at'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_statuses');
    }
};
