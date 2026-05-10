<?php

namespace App\Events;

use App\Models\StationaryRequest;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequestStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $request;
    public $user;
    public $action; // 'approved', 'rejected', 'created'

    /**
     * Create a new event instance.
     */
    public function __construct(StationaryRequest $request, User $user, string $action)
    {
        $this->request = $request;
        $this->user = $user;
        $this->action = $action;
    }
}
