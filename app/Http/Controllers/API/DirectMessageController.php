<?php

namespace App\Http\Controllers\API;

use App\Models\Chat;
use App\Events\MessageEvent;
use Illuminate\Http\Request;
use App\Models\DirectMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendDirectMessageRequest;

class DirectMessageController extends Controller
{
    public function send(SendDirectMessageRequest $request){

        $user = auth()->user();

        if(!$request->has('chat_id')){
            $chat = new Chat();
            $chat->model = 'Direct';
            $chat->model_id = $user->id;
            $chat->save();

            $message = new DirectMessages();
            $message->sender_id = $user->id;
            $message->chat_id = $chat->id;
            $message->reciever_id = $request->reciever_id;
            $message->message = $request->message;

            $message->save();
            event(new MessageEvent($message));
            return response()->json([
                'status' => true,
                'message' => $message
            ],200);

        }else{
            $message = new DirectMessages();
            $message->sender_id = $user->id;
            $message->chat_id = $request->chat_id;
            $message->reciever_id = $request->reciever_id;
            $message->chat_id = $request->chat_id;
            $message->message = $request->message;

            $message->save();

            event(new MessageEvent($message));
            return response()->json([
                'status' => true,
                'message' => $message

            ],200);
        }


    }
}
