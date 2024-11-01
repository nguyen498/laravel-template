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
        Schema::create('post_advertisings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id'); // id người tạo bài (ko phải user tương tác post)
            $table->uuid('post_id'); // post id
            $table->dateTime('expire_date')->nullable(); // Ngày hết hạn
            $table->integer('count_click')->default(0); // số lượng click
            $table->integer('count_comment')->default(0); // số lượng comment
            $table->integer('count_like')->default(0); // số lượng like
            $table->timestamps();

            // Thêm index cho các trường cần thiết
            $table->index('user_id');
            $table->index('post_id');
            $table->index('expire_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_advertisings');
    }
};
