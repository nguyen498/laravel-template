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
            $table->string('reference', 20)->nullable(); // Post code
            $table->uuid('category_id')->nullable(); // Main category
            $table->string('category_name', 200)->nullable(); // Category name
            $table->uuid('sub_category_id')->nullable(); // Sub category
            $table->string('sub_category_name', 200)->nullable(); // Sub category name

            $table->tinyInteger('display_type')->default(1); // Display type
            $table->uuid('user_id'); // User ID
            $table->string('type', 30); // 1: home owner, 2: community
            $table->tinyInteger('status')->default(1); // 0: deactive, 1: active
            $table->uuid('post_industry_id')->nullable(); // Post industry ID
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

            // Job recruitment information
            $table->string('work_position', 100)->nullable(); // Work position
            $table->float('avg_salary')->nullable(); // Average salary
            $table->float('min_salary')->nullable(); // Minimum salary
            $table->float('max_salary')->nullable(); // Maximum salary
            $table->tinyInteger('type_salary')->nullable(); // Salary type
            $table->string('job_type', 20)->nullable(); // Job type
            $table->text('job_contract')->nullable(); // Labor contract
            $table->text('job_time')->nullable(); // JSON for working hours
            $table->float('job_experience')->nullable(); // Work experience
            $table->text('require_skill')->nullable(); // JSON for required skills
            $table->text('advance_skill')->nullable(); // JSON for advanced skills
            $table->text('job_environmental')->nullable(); // Working environment

            // Sales information
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

            $table->softDeletes(); // Soft delete for 'deleted_at'
            $table->timestamps(); // Created at and updated at timestamps

            $table->index('user_id'); // Chỉ mục cho user_id
            $table->index('category_id'); // Chỉ mục cho category_id
            $table->index('sub_category_id'); // Chỉ mục cho sub_category_id
            $table->index('post_industry_id'); // Chỉ mục cho post_industry_id
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
