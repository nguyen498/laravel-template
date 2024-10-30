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
        Schema::create('user_group_chats', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID as primary key
            $table->uuid('post_id'); // Post ID
            $table->string('title', 255); // Title
            $table->string('post_logo')->nullable(); // Post logo
            $table->uuid('actor_id'); // Actor ID (the one who initiates the chat)
            $table->string('actor_name', 100); // Actor name
            $table->string('actor_logo')->nullable(); // Actor logo
            $table->uuid('user_id'); // User ID (the one to chat with)
            $table->string('user_logo')->nullable(); // User logo
            $table->string('user_name', 100); // User name
            $table->timestamps(); // Created at and updated at

            // Thêm chỉ mục
            $table->index(['post_id', 'actor_id', 'user_id']);
            $table->index(['post_id', 'actor_id']);
            $table->index(['post_id', 'user_id']);
            $table->index('post_id');
            $table->index('actor_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_group_chats');
    }
};
