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
        Schema::create('filter_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('filter_id')->nullable();; // mã filter
            $table->string('field_name', 255)->nullable();; // tên filter
            $table->string('label', 100)->nullable();; // nhãn
            $table->text('data')->nullable();; // dữ liệu cho column
            $table->string('default_value', 100)->nullable(); // dữ liệu mặc định
            $table->string('placeholder', 255)->nullable(); // placeholder
            $table->string('field_type', 100)->nullable();; // loại field
            $table->timestamps();

            // Thêm index cho các trường cần thiết
            $table->index('filter_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filter_details');
    }
};
