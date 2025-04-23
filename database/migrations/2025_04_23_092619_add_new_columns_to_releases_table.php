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
        Schema::table('releases', function (Blueprint $table) {
            $table->string('file_release_name')->nullable();
            $table->string('file_cover_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('release', function (Blueprint $table) {
            $table->dropColumn('file_release_name');
            $table->dropColumn('file_cover_name');
        });
    }
};
