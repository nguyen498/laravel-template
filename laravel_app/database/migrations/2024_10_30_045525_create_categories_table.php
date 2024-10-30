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
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID as primary key
            $table->string('reference', 20)->nullable(); // Menu code
            $table->string('name', 200); // Menu name (e.g., sales, jobs)
            $table->text('description')->nullable(); // Description
            $table->tinyInteger('type'); // 1: home owner, 2: community
            $table->tinyInteger('status')->default(1); // Status
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
