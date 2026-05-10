<?php

namespace App\Listeners;

use App\Events\RequestStatusChanged;
use App\Models\ActivityLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogRequestActivity
{
    /**
     * Handle the event.
     */
    public function handle(RequestStatusChanged $event): void
    {
        $request = $event->request;
        $user = $event->user;

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => $event->action,
            'model_type' => get_class($request),
            'model_id' => $request->id,
            'changes' => [
                'status' => $request->status,
                'role' => $user->roles->first()->name ?? 'unknown',
            ],
            'ip_address' => request()->ip(),
        ]);
    }
}
