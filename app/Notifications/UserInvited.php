<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use App\Project;
use App\User;
use App\Invite;

class UserInvited extends Notification implements ShouldQueue
{
    use Queueable;

    private $refer;
    private $invite;
    private $project;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $refer, $invite, Project $project)
    {
        $this->refer   = $refer;
        $this->invite  = $invite;
        $this->project = $project;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if ($this->invite instanceof User) {
            return (new MailMessage)
                ->subject($this->refer->name . ' invited you to ' . $this->project->name)
                ->greeting('Hello ' . $this->invite->name . '!')
                ->line($this->refer->name . ' invited you to collaborate on project ' . $this->project->name . '.')
                ->action('View Project', route('project.dashboard', [$this->project]))
                ->line('Thank you for using Cyrano!');
        }

        if ($this->invite instanceof Invite) {
            return (new MailMessage)
                ->subject($this->refer->name . ' invited you to Cyrano')
                ->greeting('Hello!')
                ->line($this->refer->name . ' invited you to collaborate on project ' . $this->project->name . '.')
                ->action('Join ' . $this->refer->name . ' now!', route('auth.invitation', [$this->invite->code]))
                ->line('Thank you for using Cyrano!');
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        if ($this->invite instanceof User) {
            return [
                'project_id'   => $this->project->id,
                'project_name' => $this->project->name,
                'user_id'      => $this->invite->id,
                'user_name'    => $this->invite->name,
                'refer_id'     => $this->refer->id,
                'refer_name'   => $this->refer->name,
            ];
        }

        if ($this->invite instanceof Invite) {
            return [
                'project_id'   => $this->project->id,
                'project_name' => $this->project->name,
                'invite_id'    => $this->invite->id,
                'invite_email' => $this->invite->email,
                'refer_id'     => $this->refer->id,
                'refer_name'   => $this->refer->name,
            ];
        }
    }
}
