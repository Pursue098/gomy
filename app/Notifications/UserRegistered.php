<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use App\User;

class UserRegistered extends Notification
{
    use Queueable;

    private $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
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
        return ['mail'];
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

        $url = url('/');
        return (new MailMessage)
                ->subject('Verify Cyrano Account')
                ->greeting('Hello, ')
                ->line('Your Cyrano account has been created. To access your account, please, verify your account.')
                ->action('Verify Account', route('email-verification.check', $this->user->verification_token) . '?email=' . urlencode($this->user->email))
//                ->action('Verify Account', 'https://laravel.com')
                ->line('Thank you for using Cyrano!');
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
//            'refer_name' =>'You have '
        ];
    }
}
