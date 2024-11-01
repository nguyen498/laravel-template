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
        Schema::create('configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255)->nullable();; // tên cấu hình
            $table->longText('value')->nullable();; // giá trị
            $table->longText('data')->nullable(); // dữ liệu
            $table->tinyInteger('type')->nullable();; // loại cấu hình
            $table->tinyInteger('status')->nullable();; // trạng thái cấu hình
            $table->text('medias')->nullable(); // hình ảnh
            $table->string('slug', 255)->nullable(); // link slug
            $table->timestamps();

            // Thêm index cho các trường cần thiết
            $table->index('name');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configs');
    }
};
