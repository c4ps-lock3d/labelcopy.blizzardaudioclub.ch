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
        Schema::create('release_formats', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });
        Schema::table('releases', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\ReleaseFormat::class)->nullable()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('release_formats');
        Schema::table('releases', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\ReleaseFormat::class);
        });
    }
};
