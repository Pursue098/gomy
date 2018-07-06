<?php

namespace App\Notifications;

use App\Channel;
use App\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use App\User;

class WebhookNotifications extends Notification
{
    use Queueable;

    private $user;
    private $project;
    private $channel;
    private $invoice;
    private $status;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Project $project, Channel $channel, User $user, $invoice, $status)
    {
        $this->project = $project;
        $this->channel = $channel;
        $this->user = $user;
        $this->invoice = $invoice;
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
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if ($this->status == 'subscription') {
            return (new MailMessage)
                ->subject('Subscription')
                ->greeting('Hello, ')
                ->line('Your subscription is active for following channel')
                ->line(strtoupper($this->channel->type))
                ->line('Date & time')
                ->line(date("d-m-Y H:i"))
                ->action('Get Invoice', route('payment.subscription-invoice', [$this->project,  $this->invoice, $this->user]))
                ->line('Thank you for using Cyrano!');

        }elseif ($this->status == 'end_trial'){

            return (new MailMessage)
                ->subject('Trial expired')
                ->greeting('Hello, ')
                ->line('Your trial period has been expired. Please subscribe')
                ->line(strtoupper($this->channel->type))
                ->line('Date & time')
                ->line(date("d-m-Y H:i"))
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
        if($this->status == 'subscription'){
            return [
                'refer_name' => 'your subscription is active'
            ];
        }elseif ($this->status == 'end_trial') {
            return [
                'refer_name' => 'Your trial period has been expired. Please subscribe'
            ];
        }
    }
}
