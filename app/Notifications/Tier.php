<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use App\User;
use App\Channel;
use App\Project;

class Tier extends Notification
{
    use Queueable;

    private $user;
    private $status;
    private $tier;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, $tier, $status)
    {
        $this->user = $user;
        $this->tier = $tier;
        $this->status = $status;
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
//        return $notifiable->prefers_sms ? ['mail'] : ['broadcast', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if ($this->status == 'add_tier'){

            return (new MailMessage)
                ->subject('Success')
                ->greeting('Hello, ')
                ->line('You have added a new tier')
                ->line($this->tier->name)
                ->line('Cyrano!');

        }elseif ($this->status == 'update_tier'){

            return (new MailMessage)
                ->subject('Success')
                ->greeting('Hello, ')
                ->line('You have update a Tier')
                ->line($this->tier->name)
                ->line('Cyrano!');
        }elseif ($this->status == 'delete_tier'){

            return (new MailMessage)
                ->subject('Success')
                ->greeting('Hello, ')
                ->line('You have deleted a Tier')
                ->line($this->tier->name)
                ->line('Cyrano!');
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
        if($this->status == 'add_tier'){
            return [
                'refer_name' => 'You have added a new tier'
            ];
        }elseif ($this->status == 'update_tier') {

            return [
                'refer_name' =>'You have update a tier'
            ];
        }elseif ($this->status == 'delete_tier') {
            return [
                'refer_name' =>'You have deleted a tier'
            ];
        }
    }
}
