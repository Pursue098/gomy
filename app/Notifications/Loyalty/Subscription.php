<?php

namespace App\Notifications\Loyalty;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use Illuminate\Encryption\Encrypter;

use App\Project;

class Subscription extends Notification
{
    use Queueable;

    public $project;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Project $project)
    {
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
        if (isset($notifiable->email))
            return ['mail'];

        return [TwilioChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $c = new Encrypter(base64_decode('h8SMiUtvbyNqQemYOfkjPiMx82/39fnBGjsXxbBGXfY='), 'AES-256-CBC');

        $url = \Bitly::getUrl('https://users.cyranocrm.it/landing/' . $this->project->getRouteKey() . '/' . urlencode($c->encrypt($notifiable->uuid)) . '/subscribe?email=' . urlencode($c->encrypt($notifiable->email)));

        return (new MailMessage)
            ->subject('Loyalty Registration')
            ->line('Please confirm you email address before subscribing to our Loyalty Program')
            ->action('Confirm', $url)
            ->line('Thank you for using our Loyalty Program!');
    }

    /**
     * Get the Nexmo / SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return NexmoMessage
     */
    public function toTwilio($notifiable)
    {
        $c = new Encrypter(base64_decode('h8SMiUtvbyNqQemYOfkjPiMx82/39fnBGjsXxbBGXfY='), 'AES-256-CBC');

        $url = \Bitly::getUrl('https://users.cyranocrm.it/landing/' . $this->project->getRouteKey() . '/' . urlencode($c->encrypt($notifiable->uuid)) . '/subscribe?phone=' . urlencode($c->encrypt($notifiable->phone)));

        return (new TwilioSmsMessage)->content('Confirm Loyalty ' . $url);
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
            //
        ];
    }
}
