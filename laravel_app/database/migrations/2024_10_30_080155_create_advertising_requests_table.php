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
        Schema::create('advertising_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id'); // id người tạo
            $table->string('user_name', 255)->nullable();; // tên người tạo
            $table->string('user_phone', 20)->nullable();; // số đt người tạo
            $table->string('post_id', 100); // id bài post
            $table->string('post_name', 255)->nullable();; // tên bài post
            $table->dateTime('start_date')->nullable();; // ngày bắt đầu quảng cáo
            $table->dateTime('expire_date')->nullable();; // ngày hết hạn
            $table->text('notes')->nullable(); // Ghi chú
            $table->tinyInteger('adv_type')->nullable();; // loại quảng cáo
            $table->tinyInteger('type')->default(0); // loại mặc định
            $table->tinyInteger('status')->nullable();; // trạng thái
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
        Schema::dropIfExists('advertising_requests');
    }
};
