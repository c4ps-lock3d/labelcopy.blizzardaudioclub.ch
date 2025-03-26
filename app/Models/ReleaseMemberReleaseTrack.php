<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReleaseMemberReleaseTrack extends Model
{
    protected $guarded = ['id'];

    public function release_members()
    {
        return $this->belongsTo(ReleaseMember::class);
    }

    public function release_tracks()
    {
        return $this->belongsTo(ReleaseTrack::class);
    }
}