<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use App\User;


class Plan extends Notification
{
    use Queueable;

    private $status;
    private $plan;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct( $plan, $status)
    {
        $this->plan = $plan;
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
        if ($this->status == 'add_plan'){

            return (new MailMessage)
                ->subject('Success')
                ->greeting('Hello, ')
                ->line('You have added a new plan')
                ->line($this->plan->name)
                ->line('Cyrano!');

        }elseif ($this->status == 'update_plan'){

            return (new MailMessage)
                ->subject('Success')
                ->greeting('Hello, ')
                ->line('You have update this Plan')
                ->line($this->plan->plan_name)
                ->line('Cyrano!');
        }elseif ($this->status == 'delete_plan'){

            return (new MailMessage)
                ->subject('Success')
                ->greeting('Hello, ')
                ->line('You have deleted this Plan')
                ->line($this->plan->plan_name)
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
        if($this->status == 'add_plan'){
            return [
                'refer_name' => 'You have added a new Plan'
            ];
        }elseif ($this->status == 'update_plan') {

            return [
                'refer_name' =>'You have update a plan'
            ];
        }elseif ($this->status == 'delete_plan') {
            return [
                'refer_name' =>'You have deleted a plan'
            ];
        }
    }
}
