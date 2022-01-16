<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;

class GroupMessageController extends Controller
{
    public function index($group_id){
        $group = Group::findOrFail($group_id);
        $group->messages;

        return response()->json([
            'status' => true,
            'group' => $group
        ],200);
    }

    public function send(SendMessageRequest $request){
        $message = new GroupMessage();
        $message->group_id = $request->group_id;
        $message->chat_id = $request->chat_id;
        $message->message = $request->message;
        $message->user_id = auth()->user()->id;

        $message->save();

        $message->user = User::findOrFail($message->user_id);
        $message->user->avatar = asset('assets/profile/'.$message->user->profile_pic);
        return response()->json([
            'status' => true,
            'message' => $message,
        ],200);
    }
}
