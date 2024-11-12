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
        Schema::create('post_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->uuid('post_id')->index();
            $table->enum('type', ['recruitment', 'search_job'])->default('search_job');
            // Job recruitment information
            $table->string('work_position', 100)->nullable(); // Work position
            $table->float('avg_salary')->nullable(); // Average salary
            $table->float('min_salary')->nullable(); // Minimum salary
            $table->float('max_salary')->nullable(); // Maximum salary
            $table->tinyInteger('type_salary')->nullable(); // Salary type
            $table->string('job_type', 20)->nullable(); // Job type
            $table->text('job_contract')->nullable(); // Labor contract
            $table->text('job_time')->nullable(); // JSON for working hours
            $table->float('job_experience')->nullable(); // Work experience
            $table->text('require_skill')->nullable(); // JSON for required skills
            $table->text('advance_skill')->nullable(); // JSON for advanced skills
            $table->text('job_environmental')->nullable(); // Working environment
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_jobs');
    }
};
