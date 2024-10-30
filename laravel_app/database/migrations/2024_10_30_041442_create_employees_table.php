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
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID as primary key
            $table->string('username', 100)->unique(); // Unique username
            $table->string('password', 120); // Password for authentication
            $table->string('fullname', 100); // Full name of the employee
            $table->string('email', 255)->unique(); // Unique email
            $table->string('phone', 20)->nullable(); // Phone number
            $table->text('description')->nullable(); // Description
            $table->softDeletes(); // Soft delete timestamp
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
