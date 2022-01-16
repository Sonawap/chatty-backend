<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Group;
use App\Models\GroupMember;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddMemberRequest;
use App\Http\Requests\RemoveMemberRequest;
use App\Http\Requests\AddBulkMemberRequest;

class GroupMemberController extends Controller
{
    public function index($group_id){
        $group = Group::findOrFail($group_id);
        $group->members;

        return response()->json([
            'status' => true,
            'group' => $group
        ],200);
    }

    public function add(AddMemberRequest $request){
        $user = User::findOrFail($request->user_id);

        if($user == auth()->user()){
            return response()->json([
                'status' => false,
                'message' => 'you cannot add yourself to this group'
            ]);
        }

        $group = Group::findOrFail($request->group_id);
        if($group->checkifUserIsAMember($user)){
            return response()->json([
                'status' => false,
                'message' => 'user is already a member'
            ]);
        }

        $member = new GroupMember();
        $member->group_id = $group->id;
        $member->user_id = $user->id;
        $member->save();

        return response()->json([
            'status' => true,
            'message' => 'User added to group'
        ]);
    }

    public function addBulk(AddBulkMemberRequest $request){
        $ids = explode(',', $request->ids);
        $users = User::WhereIn('id', $ids)->get();
        $group = Group::findOrFail($request->group_id);

        $users->each(function($user) use ($group){
            $member = new GroupMember();
            $member->group_id = $group->id;
            $member->user_id = $user->id;
            $member->save();
            return $user;
        });
        return response()->json([
            'status' => true,
            'message' => 'User added to group'
        ]);
    }

    public function remove(RemoveMemberRequest $request){
        $member = GroupMember::findOrFail($request->id);
        $member->delete();

        return response()->json([
            'status' => true,
            'message' => 'User removed to group'
        ]);
    }
}
