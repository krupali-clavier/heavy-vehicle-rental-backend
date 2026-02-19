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
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('phone')->nullable(); // Can be used without user_id for new registrations
            $table->string('email')->nullable(); // Can be used without user_id for new registrations
            $table->string('otp', 6);
            $table->string('type')->default('phone_verification'); // phone_verification, password_reset, etc.
            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamps();

            $table->index(['phone', 'otp', 'is_used']);
            $table->index(['email', 'otp', 'is_used']);
            $table->index(['user_id', 'is_used']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
