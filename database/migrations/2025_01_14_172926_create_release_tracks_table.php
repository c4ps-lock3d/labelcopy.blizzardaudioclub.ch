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
        Schema::create('release_tracks', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('number')->nullable();
            $table->string('title')->nullable();
            $table->boolean('isSingle')->nullable();
            $table->boolean('hasClip')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('release_tracks');
    }
};
