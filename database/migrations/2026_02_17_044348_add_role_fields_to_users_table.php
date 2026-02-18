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
            // Note: Roles are now managed by Spatie Laravel Permission package
            // No need for role enum field in users table
            $table->string('phone')->nullable()->after('email');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->boolean('is_verified')->default(false)->after('phone_verified_at');
            // Note: OTP fields moved to separate 'otps' table
            $table->text('address')->nullable()->after('is_verified');
            $table->string('profile_image')->nullable()->after('address');
            $table->decimal('hourly_rate', 10, 2)->default(0)->nullable()->after('profile_image');
            $table->boolean('is_available')->default(true)->after('hourly_rate');
            $table->enum('status', ['pending', 'active', 'inactive', 'rejected', 'suspended'])->default('pending')->after('is_available');
            $table->integer('total_trips')->default(0)->after('status');
            $table->decimal('rating', 3, 2)->default(0)->nullable()->after('total_trips');
            $table->text('bio')->nullable()->after('rating');
            $table->string('license_number')->nullable()->unique()->after('bio');
            $table->string('license_type')->nullable()->after('license_number');
            $table->date('license_expiry_date')->nullable()->after('license_type');
            $table->string('license_image')->nullable()->after('license_expiry_date');
            $table->softDeletes()->after('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'phone_verified_at',
                'is_verified',
                'address',
                'profile_image',
                'license_number',
                'license_type',
                'license_expiry_date',
                'license_image',
                'hourly_rate',
                'is_available',
                'driver_status',
                'total_trips',
                'rating',
                'bio',
                'deleted_at',
            ]);
        });
    }
};
