<?php

namespace App\Models;

use App\Models\Group;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function groups(){
        return $this->hasMany(Group::class)->orderBy('created_at', 'desc');
    }

    public function getAvatarAttribute($pic) {
        return asset('assets/profile/'.$pic);
    }

    public function getWithGroups(){
        return $this;
    }

    public function getUserGroups(){
        $check = GroupMember::where('user_id', $this->id)->pluck('group_id')->toArray();
        $groups= Group::whereIn('id', $check)->latest()->get();
        $groups->each(function($group){
            $group->chat = Chat::where('model_id', $group->id)->first();
            return $group;
        });
        return $groups;
    }
}
