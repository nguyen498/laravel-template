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
        Schema::create('medias', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID as primary key
            $table->string('name', 200)->nullable(); // Name of the media
            $table->string('path', 255)->nullable(); // Path to the media file
            $table->float('width')->nullable(); // Width of the media
            $table->float('height')->nullable(); // Height of the media
            $table->tinyInteger('type')->nullable(); // Type of media (image, video, etc.)
            $table->integer('sort')->default(0); // Sorting order
            $table->string('mediaable_reference', 20)->nullable(); // Reference for polymorphic relation
            $table->uuid('mediaable_id')->nullable(); // ID for polymorphic relation
            $table->string('mediaable_type', 200)->nullable(); // Type for polymorphic relation
            $table->timestamps(); // Created at and updated at timestamps

            $table->index(['mediaable_id', 'mediaable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medias');
    }
};
