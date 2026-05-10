<?php

namespace App\Notifications;

use App\Models\StationaryRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestRequiresApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $request;

    public function __construct(StationaryRequest $request)
    {
        $this->request = $request;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Stationary Request Requires Your Approval')
            ->line('A stationary request from ' . $this->request->requestedBy->name . ' requires your approval.')
            ->line('Total Amount: $' . number_format($this->request->total_amount, 2))
            ->action('Review Request', route('requests.show', $this->request))
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'request_id' => $this->request->id,
            'message' => 'Request #' . $this->request->id . ' requires your approval.',
            'url' => route('requests.show', $this->request),
        ];
    }
}
