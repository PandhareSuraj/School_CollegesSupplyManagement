<?php

namespace App\Notifications;

use App\Models\StationaryRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class RequestStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $request;
    public $action;

    public function __construct(StationaryRequest $request, string $action)
    {
        $this->request = $request;
        $this->action = $action;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusStr = str_replace('_', ' ', Str::title($this->request->status));
        return (new MailMessage)
            ->subject('Your Stationary Request Status: ' . $statusStr)
            ->line('Your stationary request #' . $this->request->id . ' has been ' . $this->action . '.')
            ->line('Current Status: ' . $statusStr)
            ->action('View Request', route('requests.show', $this->request));
    }

    public function toArray(object $notifiable): array
    {
        $statusStr = str_replace('_', ' ', Str::title($this->request->status));
        return [
            'request_id' => $this->request->id,
            'message' => 'Your request #' . $this->request->id . ' is now ' . $statusStr,
            'url' => route('requests.show', $this->request),
        ];
    }
}
