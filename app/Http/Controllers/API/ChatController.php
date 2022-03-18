<?php

namespace App\Http\Controllers\API;

use App\Models\Chat;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupMessage;
use Illuminate\Http\Request;
use App\Models\DirectMessages;
use App\Http\Controllers\Controller;

class ChatController extends Controller
{
    public function group(Request $request){
        $group_id = $request->group_id;
        $chat = Chat::where('model', 'Group')->where('model_id', $group_id)->first();
        $chat->messages = GroupMessage::where('chat_id', $chat->id)->orderBy('created_at', 'asc')->get();
        $chat->messages->each(function($message){
            $message->user = User::findOrFail($message->user_id);
            return $message;
        });

        return response()->json([
            'status' => true,
            'chat' => $chat
        ], 200);
    }

    public function direct(Request $request){
        $chat = Chat::where('id', $request->chat_id)->first();
        if($request->type == "Direct"){
            $chat->messages = DirectMessages::where('chat_id', $chat->id)->orderBy('created_at', 'asc')->get();
            $chat->messages->each(function($message){
                $message->user = User::findOrFail($message->sender_id);
                return $message;
            });
            return response()->json([
                'status' => true,
                'chat' => $chat
            ], 200);
        }elseif($request->type == "Group"){
            $chat->messages = GroupMessage::where('chat_id', $chat->id)->orderBy('created_at', 'asc')->get();
            $chat->messages->each(function($message){
                $message->user = User::findOrFail($message->user_id);
                return $message;
            });
            return response()->json([
                'status' => true,
                'chat' => $chat
            ], 200);
        }


    }

    public function allChats(Request $request){
        $user = auth()->user();
        $groups = $user->getUserGroups()->pluck('id');

        $sender = DirectMessages::where('sender_id', $user->id)->orWhere('reciever_id', $user->id)->pluck('chat_id');



        $chats = Chat::where(function($query) use ($groups, $sender){
            $query->whereIn('model_id', $groups)->
            orWhereIn('id', $sender);
        })->orderBy('created_at', 'desc')->get();


        $chats->each(function($chat) use ($user){
            if($chat->model == "Group"){
                $chat->object = Group::findOrFail($chat->model_id);
                $chat->messages = GroupMessage::where('chat_id', $chat->id)->orderBy('created_at', 'desc')->get();
                $chat->messages->each(function($message){
                    $message->user = User::findOrFail($message->user_id);
                    return $message;
                });
            }
            if($chat->model == "Direct"){
                $chat->messages = DirectMessages::where('chat_id', $chat->id)->orderBy('created_at', 'desc')->get();
                $chat->object = DirectMessages::where('chat_id', $chat->id)->where('reciever_id', '!=', $user->id)->orderBy('created_at', 'desc')->first();
                $chat->object = User::findOrFail($chat->object->reciever_id);

            }
        });


        return response()->json([
            'status' => true,
            'chats' => $chats
        ], 200);
    }
}
