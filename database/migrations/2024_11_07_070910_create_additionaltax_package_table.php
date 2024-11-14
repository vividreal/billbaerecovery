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
        Schema::create('additionaltax_package', function (Blueprint $table) {
            $table->id();
            $table->foreignId('additionaltax_id')->constrained('additionaltaxes'); // Foreign key to 'additionaltaxes' table
            $table->foreignId('package_id')->constrained('packages'); // Foreign key to 'packages' table
            $table->timestamps(0); // created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('additionaltax_package');
    }
};
