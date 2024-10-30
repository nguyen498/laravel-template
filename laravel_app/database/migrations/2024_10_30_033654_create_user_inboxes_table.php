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
        Schema::create('user_inboxes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('title', 255);
            $table->text('content')->nullable();
            $table->text('data');
            $table->tinyInteger('type')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->string('inbox_id', 36)->nullable(); // ID of related inbox
            $table->string('inbox_type', 50)->nullable(); // Type of related inbox
            $table->integer('attempt')->default(0); // Attempts for queue
            $table->softDeletes(); // deleted_at timestamp
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_inboxes');
    }
};
