<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Group;
use App\Events\MessageEvent;
use App\Events\MessageNotificationEvent;
use App\Models\GroupMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Notifications\MessageNotification;
use Illuminate\Support\Facades\Notification;

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

        event(new MessageEvent($message));
        event(new MessageNotificationEvent($message));


        return response()->json([
            'status' => true,
        ],200);
    }
}
