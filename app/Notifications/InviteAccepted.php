<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use App\Invite;
use App\User;

class InviteAccepted extends Notification implements ShouldQueue
{
    use Queueable;

    private $invite;
    private $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, Invite $invite)
    {
        $this->invite = $invite;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'project_id'   => $this->invite->project->id,
            'project_name' => $this->invite->project->name,
            'user_id'      => $this->user->id,
            'user_name'    => $this->user->name,
        ];
    }
}
