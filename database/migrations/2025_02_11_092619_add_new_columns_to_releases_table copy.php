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
        Schema::table('release_members', function (Blueprint $table) {
            $table->boolean('is_reference')->default(false);
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('phone_number')->nullable();
            $table->date('birth_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('release_members', function (Blueprint $table) {
            $table->dropColumn('is_reference');
            $table->dropColumn('street');
            $table->dropColumn('city');
            $table->dropColumn('zip_code');
            $table->dropColumn('phone_number');
            $table->dropColumn('birth_date');
        });
    }
};
