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
        Schema::create('user_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id'); // id người dùng
            $table->uuid('post_id'); // id post
            $table->tinyInteger('type'); // 1: interest, 2: favorite, 3: ...
            $table->boolean('is_notification')->default(false); // Thông báo
            $table->timestamps();

            // Thêm index cho các trường cần thiết
            $table->index('user_id');
            $table->index('post_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_posts');
    }
};
