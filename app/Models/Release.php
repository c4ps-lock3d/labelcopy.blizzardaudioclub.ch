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

    public function release_format (){
        return $this->belongsTo(ReleaseFormat::class);
    }
    public function release_type (){
        return $this->belongsTo(ReleaseType::class);
    }
    public function release_track (){
        return $this->belongsTo(ReleaseTrack::class);
    }
    public function release_social (){
        return $this->belongsTo(ReleaseSocial::class);
    }
    public function release_member (){
        return $this->belongsTo(ReleaseMember::class);
    }
}
