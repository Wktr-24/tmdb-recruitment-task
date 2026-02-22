<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('tmdb_id')->unique();
            $table->json('name');
            $table->json('overview');
            $table->date('first_air_date')->nullable();
            $table->string('poster_path')->nullable();
            $table->decimal('vote_average', 3, 1)->nullable();
            $table->unsignedInteger('vote_count')->default(0);
            $table->decimal('popularity', 10, 3)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};
