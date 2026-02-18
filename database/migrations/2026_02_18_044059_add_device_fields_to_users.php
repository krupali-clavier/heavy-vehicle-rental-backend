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
            $table->string('social_login_name')->nullable()->after('license_image');
            $table->string('social_login_token')->nullable()->after('social_login_name');
            $table->string('device_id')->nullable()->after('social_login_token');
            $table->string('device_token')->nullable()->after('device_id');
            $table->string('device_name')->nullable()->after('device_token');
            $table->string('device_type')->nullable()->after('device_name');
            $table->string('fcm_token')->nullable()->after('device_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['social_login_name', 'social_login_token', 'device_id', 'device_token', 'device_name', 'device_type', 'fcm_token']);
        });
    }
};
