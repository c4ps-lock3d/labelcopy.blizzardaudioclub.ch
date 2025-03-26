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
        Schema::create('releasemember_releasetrack', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\ReleaseMember::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\ReleaseTrack::class)->constrained()->cascadeOnDelete();
            $table->primary(['release_member_id','release_track_id']);
            $table->integer('percentage')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_track');
    }
};
