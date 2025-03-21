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
            $table->boolean('isProduitsDerives')->nullable();
            $table->boolean('isBesoinSubvention')->nullable();
            $table->boolean('isBesoinBooking')->nullable();
            $table->boolean('isBesoinPromo')->nullable();
            $table->boolean('isBesoinDigitalMarketing')->nullable();
            $table->boolean('isBesoinContacts')->nullable();
            $table->boolean('isActive')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('release', function (Blueprint $table) {
            $table->dropColumn('isProduitsDerives');
            $table->dropColumn('isBesoinSubvention');
            $table->dropColumn('isBesoinBooking');
            $table->dropColumn('isBesoinPromo');
            $table->dropColumn('isBesoinDigitalMarketing');
            $table->dropColumn('isBesoinContacts');
            $table->dropColumn('isActive');
        });
    }
};
