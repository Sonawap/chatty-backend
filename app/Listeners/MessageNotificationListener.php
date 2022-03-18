<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\MessageNotificationEvent;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\MessageNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class MessageNotificationListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(MessageNotificationEvent $event)
    {
        $ids = $event->message->group->members->pluck('user_id')->toArray();
        $members = User::whereIn('id', $ids)->get();
        Notification::send($members, new MessageNotification(auth()->user(), $event));

    }
}
