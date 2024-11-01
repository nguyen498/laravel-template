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
        Schema::create('user_recent_searches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();; // id user
            $table->string('keyword', 100)->nullable();; // keyword search
            $table->text('location')->nullable();; // json object
            $table->text('data_search')->nullable();; // gửi elastic search sau khi format
            $table->longText('data_raw')->nullable();; // data raw được save từ filter
            $table->integer('num_new_post')->default(0); // count num new post
            $table->timestamps();

            // Thêm index cho các trường cần thiết
            $table->index('user_id');
            $table->index('keyword');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_recent_searches');
    }
};
