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
            $table->integer('price')->nullable();
            $table->string('style')->nullable();
            $table->longText('artistBiography')->nullable();
            $table->longText('description')->nullable();
            $table->string('credits')->nullable();
            $table->string('remerciements')->nullable();
            $table->string('remarques')->nullable();
            $table->string('envies')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('releases', function (Blueprint $table) {
            $table->dropColumn('price');
            $table->dropColumn('style');
            $table->dropColumn('artistBiography');
            $table->dropColumn('description');
            $table->dropColumn('credits');
            $table->dropColumn('remerciements');
            $table->dropColumn('remarques');
            $table->dropColumn('envies');
        });
    }
};
