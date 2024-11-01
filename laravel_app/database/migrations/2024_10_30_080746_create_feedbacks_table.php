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
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_id', 36)->nullable(); // id người dùng
            $table->string('reference', 30)->unique(); // mã feedback
            $table->string('title', 255); // tiêu đề
            $table->longText('content')->nullable(); // Nội dung feedback
            $table->tinyInteger('type')->nullable(); // loại hỗ trợ
            $table->tinyInteger('status')->nullable(); // trạng thái
            $table->text('medias')->nullable(); // hình ảnh
            $table->string('confirm_id', 36)->nullable(); // người xác nhận (employee id)
            $table->string('confirm_name', 255)->nullable(); // tên người xác nhận
            $table->timestamps();

            // Thêm index cho các trường cần thiết
            $table->index('user_id');
            $table->index('reference');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
