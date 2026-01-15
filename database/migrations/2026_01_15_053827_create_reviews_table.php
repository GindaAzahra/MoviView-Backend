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
        Schema::create('reviews', function (Blueprint $table) {
            $table->string('id_review')->primary();
            $table->string('id_user')->nullable();
            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->nullOnDelete();
            $table->string('id_movie');
            $table->string('rating');
            $table->string('review');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
