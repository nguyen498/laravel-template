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
        Schema::create('post_sales', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->uuid('post_id')->index();
            $table->enum('type', ['sell', 'buy'])->default('sell');
            $table->string('business_type', 100)->nullable(); // Business type
            $table->string('facebook_name', 100)->nullable(); // Facebook name
            $table->string('facebook_url', 255)->nullable(); // Facebook link
            $table->string('instagram_name', 100)->nullable(); // Instagram name
            $table->string('instagram_url', 255)->nullable(); // Instagram link
            $table->text('facilities')->nullable(); // Facilities
            $table->integer('num_employees')->nullable(); // Number of employees
            $table->float('price')->nullable(); // Price (0 means negotiable)
            $table->text('lease_agreement')->nullable(); // Lease agreement
            $table->float('avg_revenue')->nullable(); // Average revenue
            $table->tinyInteger('support')->nullable(); // Null | 1: training for new owners | 2: discount
            $table->text('additional_infor')->nullable(); // Additional information
            $table->text('nearby_areas')->nullable(); // Nearby areas information
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_sales');
    }
};
