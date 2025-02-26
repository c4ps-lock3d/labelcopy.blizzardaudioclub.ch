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
        Schema::create('release_release_social', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Release::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\ReleaseSocial::class)->constrained()->cascadeOnDelete();
            $table->primary(['release_id','release_social_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('release_release_social');
    }
};
