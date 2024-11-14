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
        Schema::create('rebooks', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_bill_id')->nullable(); // Foreign key to parent bill, nullable
            $table->integer('child_bill_id')->nullable(); // Foreign key to child bill, nullable
            $table->double('amount')->nullable(); // Amount field, nullable
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rebooks');
    }
};
