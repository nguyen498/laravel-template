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
        Schema::create('user_action_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id'); // id người dùng
            $table->uuid('post_id'); // id post
            $table->uuid('category_id'); // id danh mục
            $table->integer('count')->default(0); // số lượng hành động
            $table->timestamps();

            // Thêm index cho các trường cần thiết
            $table->index('user_id');
            $table->index('post_id');
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_action_posts');
    }
};
