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
        Schema::create('user_medias', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('name', 20)->nullable();
            $table->string('path', 200)->nullable();
            $table->integer('sort')->default(0); // Sorting order
            $table->tinyInteger('type')->nullable(); // Type: video or image
            $table->softDeletes(); // deleted_at timestamp for soft delete
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_medias');
    }
};
