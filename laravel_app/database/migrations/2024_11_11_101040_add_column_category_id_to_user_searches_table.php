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
        Schema::table('user_searches', function (Blueprint $table) {
            $table->string('category_id',36)->nullable();
            $table->string('sub_category_id',36)->nullable();
            $table->string('post_industry_id',36)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_searches', function (Blueprint $table) {
            $table->dropColumn([
                'category_id',
                'sub_category_id',
                'post_industry_id'
            ]);
        });
    }
};
