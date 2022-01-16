<?php

namespace App\Http\Controllers\API;

use App\Models\Chat;
use App\Models\User;
use App\Models\GroupMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChatController extends Controller
{
    public function group(Request $request){
        $group_id = $request->group_id;
        $chat = Chat::where('model', 'Group')->where('model_id', $group_id)->first();
        $chat->messages = GroupMessage::where('chat_id', $chat->id)->orderBy('created_at', 'asc')->get();
        $chat->messages->each(function($message){
            $message->user = User::findOrFail($message->user_id);
            $message->user->avatar = asset('assets/profile/'.$message->user->profile_pic);
            return $message;
        });

        return response()->json([
            'status' => true,
            'chat' => $chat
        ], 200);
    }
}
