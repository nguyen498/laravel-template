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
        Schema::create('otp_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->nullable();
            $table->string('name', 100)->nullable();
            $table->string('otp', 10)->nullable();
            $table->boolean('is_confirm')->default(false);
            $table->tinyInteger('type')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->integer('valid_in')->nullable();
            $table->tinyInteger('attempts')->default(0); // Max 5
            $table->string('device_id', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_attempts');
    }
};
