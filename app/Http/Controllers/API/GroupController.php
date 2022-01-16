<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\Chat;
use App\Models\GroupMember;
use App\Notifications\CreateGroupNotification;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();
        return response()->json([
            'status' => true,
            'groups' => $user->groups
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateGroupRequest $request){
        $group = new Group();
        $user = auth()->user();
        $group->user_id = $user->id;
        $group->name = $request->name;
        $group->description = $request->description;
        $group->save();

        $member = new GroupMember();
        $member->group_id = $group->id;
        $member->user_id = $user->id;
        $member->save();

        $users = User::whereIn('id', $request->ids)->get();
        $users->each(function($user) use ($group){
            $member = new GroupMember();
            $member->group_id = $group->id;
            $member->user_id = $user->id;
            $member->save();
        });

        $chat = new Chat();
        $chat->model = 'Group';
        $chat->model_id = $group->id;
        $chat->save();
        // $users = User::limit(100)->get();
        // Notification::send($users, new CreateGroupNotification($user));
        // Notification::send($user, new CreateGroupNotification($user));
        return response()->json([
            'status' => true,
            'group' => $group
        ],200);
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $group = Group::findOrFail($id);
        return response()->json([
            'status' => true,
            'group' => $group
        ],200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateGroupRequest $request, $id)
    {
        $group = Group::findOrFail($id);
        $group->update($request->all());
        return response()->json([
            'status' => true,
            'group' => $group
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $group = Group::findOrFail($id);
        $group->delete();
        return response()->json([
            'status' => true,
            'messgae' => 'deleted'
        ],200);
    }
}
