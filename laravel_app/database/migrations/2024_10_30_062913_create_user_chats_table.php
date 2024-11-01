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
        Schema::create('user_chats', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID as primary key
            $table->uuid('actor_id'); // Actor ID (the user ID)
            $table->string('actor_name', 100); // Actor name
            $table->longText('message'); // Message content
            $table->uuid('user_group_chat_id'); // Group chat ID
            $table->dateTime('expire_date')->nullable(); // Expiry time
            $table->text('medias')->nullable(); // Media attached to the message
            $table->tinyInteger('type')->nullable(); // Type of message
            $table->boolean('is_read')->default(false); // Read status
            $table->timestamps(); // Created at and updated at

            // Thêm chỉ mục
            $table->index('user_group_chat_id'); // Chỉ mục cho user_group_chat_id
            $table->index('actor_id'); // Chỉ mục cho user_group_chat_id
            $table->index(['actor_id', 'is_read']); // Chỉ mục cho actor_id và is_read
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_chats');
    }
};
