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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('registration_number')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('Other');
            $table->string('capacity')->nullable();
            $table->year('year')->nullable();
            $table->string('engine_power')->nullable();
            $table->enum('fuel_type', ['Diesel', 'Petrol', 'Electric', 'Hybrid', 'Gasoline', 'Other'])->nullable();
            $table->decimal('hourly_rate', 10, 2)->default(0);
            $table->decimal('daily_rate', 10, 2)->default(0);
            $table->decimal('weekly_rate', 10, 2)->nullable();
            $table->decimal('monthly_rate', 10, 2)->nullable();
            $table->boolean('driver_available')->default(true);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_address')->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance', 'rejected'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('vehicles');
        Schema::enableForeignKeyConstraints();
    }
};
