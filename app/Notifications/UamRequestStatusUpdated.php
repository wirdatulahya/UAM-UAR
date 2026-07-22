<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\UamRequest;

class UamRequestStatusUpdated extends Notification
{
    use Queueable;

    public $uamRequest;
    public $actionType;
    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(UamRequest $uamRequest, $actionType, $message)
    {
        $this->uamRequest = $uamRequest;
        $this->actionType = $actionType;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $icon = 'bi-info-circle-fill';
        $title = 'Request Update';
        
        if ($this->actionType === 'submit') {
            $icon = 'bi-send-fill';
            $title = 'New Request Submitted';
        } elseif ($this->actionType === 'approve') {
            $icon = 'bi-check-circle-fill';
            $title = 'Request Approved';
        } elseif ($this->actionType === 'return') {
            $icon = 'bi-arrow-counterclockwise';
            $title = 'Request Returned';
        } elseif ($this->actionType === 'final_approve') {
            $icon = 'bi-check2-all';
            $title = 'Request Fully Approved';
        }

        // We determine the route based on the target role viewing it
        $url = route('access-matrix.sap', ['request_id' => $this->uamRequest->id]);
        if ($notifiable->role === 'manager') {
            $url = route('access-matrix.uam-request.sap', ['request_id' => $this->uamRequest->id]);
        } elseif ($notifiable->role === 'ao') {
            $url = route('access-matrix.approval.sap', ['request_id' => $this->uamRequest->id]);
        } elseif (in_array($notifiable->role, ['pic_ao', 'admin'])) {
            $url = route('access-matrix.request.sap', ['request_id' => $this->uamRequest->id]);
        }

        return [
            'title' => $title,
            'description' => $this->message,
            'icon' => $icon,
            'url' => $url,
            'request_id' => $this->uamRequest->id,
        ];
    }
}
