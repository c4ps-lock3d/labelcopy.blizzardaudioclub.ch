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
        Schema::create('release_member_references', function (Blueprint $table) {
            $table->id();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('phone_number')->nullable();
            $table->timestamps();
        });
        Schema::table('release_members', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\ReleaseMemberReference::class)->nullable()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('release_member_references');
        Schema::table('release_members', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\ReleaseMemberReference::class);
        });
    }
};
