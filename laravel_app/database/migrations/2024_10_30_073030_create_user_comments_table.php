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
        Schema::create('user_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('parent_id')->nullable(); // comment parent id
            $table->uuid('actor_id'); // user_id
            $table->string('actor_type', 30)->nullable();; // loại user (users)
            $table->string('actor_name', 100)->nullable();; // Tên người comment
            $table->string('actor_logo', 255)->nullable();; // logo người comment
            $table->longText('message'); // Nội dung comment
            $table->string('verb', 64)->default('chat'); // default: chat
            $table->string('object_id', 36)->nullable();; // post id, ..
            $table->string('object_type', 30)->nullable();; // loại post
            $table->dateTime('expire_date')->nullable(); // ngày hết hạng
            $table->text('medias')->nullable(); // hình ảnh
            $table->tinyInteger('type')->nullable(); // loại
            $table->boolean('is_read')->default(false); // đã đọc
            $table->timestamps();

            // Thêm index cho các trường cần thiết
            $table->index(['actor_id', 'actor_type']);
            $table->index('object_id');
            $table->index('parent_id');
            $table->index('expire_date');
            $table->index('is_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_comments');
    }
};
