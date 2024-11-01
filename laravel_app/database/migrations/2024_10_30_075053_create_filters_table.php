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
        Schema::create('filters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference', 20)->unique(); // mã filter
            $table->uuid('category_id'); // mã menu
            $table->uuid('created_id')->nullable();; // id người tạo
            $table->string('created_name')->nullable();; // tên người tạo
            $table->tinyInteger('status')->nullable();; // trạng thái
            $table->timestamps();

            // Thêm index cho các trường cần thiết
            $table->index('category_id');
            $table->index('created_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filters');
    }
};
