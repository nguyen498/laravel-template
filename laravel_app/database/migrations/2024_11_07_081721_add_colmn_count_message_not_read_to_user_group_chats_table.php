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
        Schema::table('user_group_chats', function (Blueprint $table) {
            $table->integer('num_message_not_read_actor')->default(0)->nullable();
            $table->integer('num_message_not_read_user')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_group_chats', function (Blueprint $table) {
            $table->dropColumn([
                'num_message_not_read_actor',
                'num_message_not_read_user'
            ]);
        });
    }
};
