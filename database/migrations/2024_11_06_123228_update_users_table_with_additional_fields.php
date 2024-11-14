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
        Schema::table('users', function (Blueprint $table) {
            $table->string('mobile', 100)->nullable()->after('email_verified_at');
            $table->integer('phone_code')->nullable()->after('mobile');
            $table->tinyInteger('is_admin')->nullable()->after('phone_code');
            $table->string('profile')->nullable()->after('is_admin');
            $table->tinyInteger('gender')->nullable()->after('profile');
            $table->bigInteger('parent_id')->nullable()->after('gender');
            $table->unsignedBigInteger('shop_id')->nullable()->after('parent_id'); // Ensure shop_id is unsignedBigInteger
            $table->tinyInteger('is_active')->default(0)->after('shop_id');
            $table->string('verify_token')->nullable()->after('is_active');
            $table->longText('password_create_token')->nullable()->after('verify_token');
            $table->integer('updated_by')->nullable()->after('password_create_token');
            $table->timestamp('deleted_at')->nullable()->after('updated_at');

            // Add foreign key constraint for shop_id
            $table->foreign('shop_id')->references('id')->on('shops')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['shop_id']); // Drop foreign key constraint
            });
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn([
                    'mobile',
                    'phone_code',
                    'is_admin',
                    'profile',
                    'gender',
                    'parent_id',
                    'shop_id',
                    'is_active',
                    'verify_token',
                    'password_create_token',
                    'updated_by',
                    'deleted_at'
                ]);
            });
        });
    }
};
