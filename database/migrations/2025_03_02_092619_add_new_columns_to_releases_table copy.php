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
            $table->string('artistWebsite')->nullable();
            $table->mediumInteger('budget')->nullable();
            $table->text('sourceFinancement')->nullable();
            $table->text('besoinFinancement')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('tiktok')->nullable();
            $table->string('youtube')->nullable();
            $table->string('bandcamp')->nullable();
            $table->text('besoinContacts')->nullable();
            $table->string('spotify')->nullable();
            $table->string('applemusic')->nullable();
            $table->text('envies')->nullable();
            $table->text('remarques')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('release', function (Blueprint $table) {
            $table->dropColumn('artistWebsite');
            $table->dropColumn('budget');
            $table->dropColumn('sourceFinancement');
            $table->dropColumn('besoinFinancement');
            $table->dropColumn('facebook');
            $table->dropColumn('instagram');
            $table->dropColumn('tiktok');
            $table->dropColumn('youtube');
            $table->dropColumn('bandcamp');
            $table->dropColumn('besoinContacts');
            $table->dropColumn('spotify');
            $table->dropColumn('applemusic');
            $table->dropColumn('envies');
            $table->dropColumn('remarques');
        });
    }
};
