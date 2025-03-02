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
            $table->integer('budget')->nullable();
            $table->string('sourceFinancement')->nullable();
            $table->string('besoinFinancement')->nullable();
            $table->string('produitsDerives')->nullable();
            $table->string('besoinSubvention')->nullable();
            $table->string('besoinBooking')->nullable();
            $table->string('besoinPromo')->nullable();
            $table->string('besoinDigitalMarketing')->nullable();
            $table->string('besoinContacts')->nullable();
            $table->string('cleRepartition')->nullable();
            $table->string('CodeBarre')->nullable();
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
            $table->dropColumn('produitsDerives');
            $table->dropColumn('besoinSubvention');
            $table->dropColumn('besoinBooking');
            $table->dropColumn('besoinPromo');
            $table->dropColumn('besoinDigitalMarketing');
            $table->dropColumn('besoinContacts');
            $table->dropColumn('CodeBarre');
        });
    }
};
