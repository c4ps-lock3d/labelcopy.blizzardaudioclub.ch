<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReleaseTrack extends Model
{
    use HasFactory;
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = ['id'];

    public function releases(){
        return $this->belongsToMany(Release::class);
    }

    public function release_members()
    {
        return $this->belongsToMany(ReleaseMember::class, 'releasemember_releasetrack')
                    ->withPivot('percentage')
                    ->withTimestamps();
    }

    public function release_members_release_tracks()
    {
        return $this->hasMany(ReleaseMemberReleaseTrack::class);
    }
}
