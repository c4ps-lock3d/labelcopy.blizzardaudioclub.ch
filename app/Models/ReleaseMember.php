<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReleaseMember extends Model
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

/*     public function release_member_reference (){
        return $this->belongsTo(ReleaseMemberReference::class);
    } */
}
