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
        Schema::create('addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('address')->nullable(); // Short address
            $table->text('full_address')->nullable(); // Full address including district, ward, city
            $table->integer('country_id')->nullable();
            $table->string('country_name', 100)->nullable();
            $table->integer('state_id')->nullable();
            $table->string('state_name', 100)->nullable();
            $table->string('postcode', 10)->nullable(); // Postal code
            $table->tinyInteger('type')->nullable(); // Address type
            $table->tinyInteger('status')->nullable(); // Address status
            $table->uuid('addressable_id')->nullable(); // Polymorphic relation ID
            $table->string('addressable_type', 100)->nullable(); // Polymorphic relation type
            $table->string('lng', 24)->nullable(); // Longitude
            $table->string('lat', 24)->nullable(); // Latitude
            $table->softDeletes(); // Soft delete timestamp
            $table->timestamps();

            // Optional index for polymorphic relations
            $table->index(['addressable_id', 'addressable_type']);
            $table->index('state_id');
            $table->index('country_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
