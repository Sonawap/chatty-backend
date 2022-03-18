<?php

namespace App\Models;

use App\Models\User;
use App\Models\GroupMember;
use App\Models\GroupMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Group extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function members(){
        return $this->hasMany(GroupMember::class);
    }
    

    public function getAvatarAttribute($pic) {
        return asset('assets/groups/'.$pic);
    }

    public function checkifUserIsAMember(User $user){
        $check = GroupMember::where('group_id', $this->id)->where('user_id', $user->id)->exists();
        if($check){
            return true;
        }else{
            return false;
        }
    }

    public function messages(){
        return $this->hasMany(GroupMessage::class)->orderBy('created_at', 'desc');
    }
}
