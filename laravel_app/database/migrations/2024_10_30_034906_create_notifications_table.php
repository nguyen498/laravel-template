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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference', 20)->unique();
            $table->string('title', 255);
            $table->text('content')->nullable();
            $table->text('data')->nullable();
            $table->tinyInteger('type')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->tinyInteger('send_type')->nullable();
            $table->tinyInteger('send_status')->nullable();
            $table->dateTime('send_date')->nullable();
            $table->integer('attempts')->default(0);
            $table->uuid('notification_id')->nullable(); // ID of related notification
            $table->string('notification_type', 50)->nullable(); // Type of related notification
            $table->uuid('created_id')->nullable();
            $table->string('created_name', 100)->nullable();
            $table->softDeletes(); // deleted_at timestamp for soft delete
            $table->timestamps();

            $table->index(['notification_id', 'notification_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
