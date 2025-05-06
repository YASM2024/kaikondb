<?php

namespace Kaikon2\Kaikondb\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Kaikon2\Kaikondb\Models\User;

class UserLoggedIn
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $loginSuccess;
    public $email;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, $loginSuccess, $email)
    {
        //
        $this->user = $user;
        $this->loginSuccess = $loginSuccess;
        $this->email = $email;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
