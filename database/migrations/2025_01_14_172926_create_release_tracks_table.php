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
            $table->integer('number')->nullable();
            $table->string('title')->nullable();
            $table->boolean('isSingle')->nullable();
            $table->boolean('hasClip')->nullable();
            $table->timestamps();
        });
        Schema::table('releases', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\ReleaseTrack::class)->nullable()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('release_tracks');
        Schema::table('releases', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\ReleaseTrack::class);
        });
    }
};
