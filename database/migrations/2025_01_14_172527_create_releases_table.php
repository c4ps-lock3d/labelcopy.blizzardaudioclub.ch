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
        Schema::create('releases', function (Blueprint $table) {
            $table->id();
            $table->string('catalog');
            $table->string('name')->nullable();
            $table->string('artistName')->nullable();
            $table->string('artistIBAN')->nullable();
            $table->text('remerciements')->nullable();
            $table->text('credits')->nullable();
            $table->string('style')->nullable();
            $table->text('artistBiography')->nullable();
            $table->text('description')->nullable();
            $table->date('release_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('releases');
    }
};
