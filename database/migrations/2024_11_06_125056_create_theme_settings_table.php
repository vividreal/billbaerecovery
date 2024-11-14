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
        Schema::create('theme_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id'); // Shop ID as unsigned big integer
            $table->string('activeMenuColor')->nullable(); // Active menu color (nullable)
            $table->string('navbarBgColor')->nullable(); // Navbar background color (nullable)
            $table->tinyText('isNavbarDark')->nullable(); // Navbar dark (nullable)
            $table->tinyInteger('isMenuDark')->default(0); // Menu dark (default 0)
            $table->string('menuStyle', 100)->default('sidenav-active-square'); // Menu style (default 'sidenav-active-square')
            $table->tinyInteger('menuCollapsed')->default(0); // Menu collapsed (default 0)
            $table->tinyInteger('footerFixed')->default(0); // Footer fixed (default 0)
            $table->timestamps(); // created_at and updated_at

            // Foreign key constraint
            $table->foreign('shop_id')
                ->references('id')->on('shops') // Reference the id column in the shops table
                ->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->dropForeign(['shop_id']); // Drop foreign key constraint
        });
        Schema::dropIfExists('theme_settings');
    }
};
