<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Release extends Model
{
    use HasFactory;
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = ['id'];

    public function release_type (){
        return $this->belongsTo(ReleaseType::class);
    }
    public function release_formats (){
        return $this->belongsToMany(ReleaseFormat::class, 'release_release_format');
    }
    public function release_tracks (){
        return $this->belongsToMany(ReleaseTrack::class, 'release_release_track');
    }
    public function release_socials (){
        return $this->belongsToMany(ReleaseSocial::class, 'release_release_social');
    }
    public function release_members (){
        return $this->belongsToMany(ReleaseMember::class, 'release_release_member');
    }
    public function users (){
        return $this->belongsToMany(User::class, 'release_user');
    }
}
