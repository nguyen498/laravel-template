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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('push_token', 50)->nullable();
            $table->string('platform', 20)->nullable(); // android or ios
            $table->text('device_info')->nullable(); // info from devices
            $table->string('deviceable_id',36)->nullable();
            $table->string('deviceable_type', 40)->nullable();
            $table->timestamps();

            $table->index(['deviceable_id', 'deviceable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
