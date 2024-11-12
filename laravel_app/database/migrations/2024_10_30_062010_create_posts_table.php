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
        Schema::create('posts', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID as primary key
            $table->string('reference', 20)->unique(); // Post code
            $table->uuid('category_id')->nullable()->index(); // Main category
            $table->string('category_name', 200)->nullable(); // Category name
            $table->uuid('sub_category_id')->nullable()->index(); // Sub category
            $table->string('sub_category_name', 200)->nullable(); // Sub category name

            $table->tinyInteger('display_type')->default(1); // Display type
            $table->uuid('user_id')->index(); // User ID
            $table->string('type', 30); // 1: home owner, 2: community
            $table->tinyInteger('status')->default(1); // 0: deactive, 1: active
            $table->uuid('post_industry_id')->nullable()->index(); // Post industry ID
            $table->string('post_industry_name', 100)->nullable(); // Post industry name
            $table->string('title', 255); // Post title
            $table->text('description')->nullable(); // Description
            $table->string('phone_number', 20)->nullable(); // Phone number
            $table->string('email', 255)->nullable(); // Email address
            $table->string('website', 255)->nullable(); // Website link

            // General information
            $table->string('store_name', 255)->nullable(); // Store/restaurant name
            $table->text('store_address')->nullable(); // Store address
            $table->string('store_area', 255)->nullable(); // Area
            $table->text('medias')->nullable(); // JSON for images
            $table->string('slug', 255)->nullable(); // Slug for post link
            $table->string('lng', 100)->nullable();
            $table->string('lat', 100)->nullable();
            // new require
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            // Sales information
            $table->softDeletes(); // Soft delete for 'deleted_at'
            $table->timestamps(); // Created at and updated at timestamps
            $table->index('created_at'); // Chỉ mục cho created_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
