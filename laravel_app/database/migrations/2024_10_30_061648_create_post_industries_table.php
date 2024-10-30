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
        Schema::create('post_industries', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID as primary key
            $table->uuid('sub_category_id'); // Foreign key reference to sub_categories
            $table->string('reference', 20)->nullable(); // Industry code
            $table->string('name'); // Industry name
            $table->text('description')->nullable(); // Description
            $table->tinyInteger('status')->default(1); // Status
            $table->softDeletes(); // Soft delete for 'deleted_at'
            $table->timestamps(); // Created at and updated at timestamps

            $table->index('sub_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_industries');
    }
};
