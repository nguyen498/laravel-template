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
        Schema::create('sub_categories', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID as primary key
            $table->uuid('category_id'); // Foreign key reference to categories
            $table->string('name', 200); // Sub menu name (e.g., sell post, buy post, etc.)
            $table->text('description')->nullable(); // Description of the sub category
            $table->string('logo', 255)->nullable(); // Logo link for sub category
            $table->tinyInteger('status')->default(1); // Status of the sub category
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_categories');
    }
};
