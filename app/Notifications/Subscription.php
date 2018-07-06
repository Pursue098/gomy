<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use App\User;
use App\Channel;
use App\Project;

class Subscription extends Notification
{
    use Queueable;

    private $user;
    private $status;
    private $channel;
    private $project;
    private $invoice;
    private $subscription;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, Channel $channel, Project $project, $status, $invoice, $subscription )
    {
        $this->user = $user;
        $this->channel = $channel;
        $this->project = $project;
        $this->status = $status;
        $this->invoice = $invoice;
        $this->subscription = $subscription;
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
        if ($this->status == 'subscribe'){

            return (new MailMessage)
                ->subject('Thank you')
                ->greeting('Hello, ')
                ->line('You have perform subscription for following channel')
                ->line($this->channel->type)
                ->action('Get Invoice', route('payment.subscription-invoice', [$this->project, $this->invoice, $this->user]))
                ->line('Thank you for using Cyrano!');

        }elseif ($this->status == 'un_subscribe'){

            return (new MailMessage)
                ->subject('Unsubscribe')
                ->greeting('Hello, ')
                ->line('You have un-subscribed following channel')
                ->line($this->channel->type)
                ->line('Thank you for using Cyrano!');
        }elseif ($this->status == 'enterprise_un_subscribe'){

            return (new MailMessage)
                ->subject('Unsubscribe')
                ->greeting('Hello, ')
                ->line('You have un-subscribed the Enterprise plan')
                ->line($this->channel->type)
                ->line('Thank you for using Cyrano!');
        }elseif ($this->status == 'resume'){

            return (new MailMessage)
                ->subject('Resume Subscription')
                ->greeting('Hello, ')
                ->line('You have resumed to following channel')
                ->line($this->channel->type)
                ->line('Thank you for using Cyrano!');
        }elseif ($this->status == 'enterprise_resume'){

            return (new MailMessage)
                ->subject('Resume Subscription')
                ->greeting('Hello, ')
                ->line('You have resumed to following channel And use the enterprise plan')
                ->line($this->channel->type)
                ->line('Thank you for using Cyrano!');
        }elseif ($this->status == 'enterprise-enduser'){

            return (new MailMessage)
                ->subject('Enterprise Subscription')
                ->greeting('Hello, ')
                ->line('You have Request for enterprise plan. Our support team will contact you soon :)')
                ->line($this->channel->type)
                ->line('Thank you for using Cyrano!');
        }elseif ($this->status == 'enterpriseSubApproval'){

            return (new MailMessage)
                ->subject('Enterprise Subscription')
                ->greeting('Hello, ')
                ->line('Cyrano have accepted your Enterprise Subscription :)')
                ->line('Project name: ')
                ->line($this->project->name)
                ->line('Channel name: ')
                ->line($this->channel->type)
                ->line('Thank you for using Cyrano!');
        }elseif ($this->status == 'enterpriseSubUnApproval'){

            return (new MailMessage)
                ->subject('Enterprise Subscription Un-approval')
                ->greeting('Hello, ')
                ->line('Cyrano have unapproved your enterprise subscription.')
                ->line('Project name: ')
                ->line($this->project->name)
                ->line('Channel name: ')
                ->line($this->channel->type)
                ->line('Thank you for using Cyrano!');
        }elseif ($this->status == 'enterprise-admin'){

            return (new MailMessage)
                ->subject('Enterprise Subscription')
                ->greeting('Hello, ')
                ->line('User have request for Enterprise plan for following Channel')
                ->line($this->channel->type)
                ->line('User name: ')
                ->line($this->user->name)
                ->line('User email: ')
                ->line($this->user->email)
                ->line('User contact number: ')
                ->line($this->user->phone_number)
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
        if($this->status == 'subscribe'){
            return [
                'refer_name' => 'You have performed subscription'
            ];
        }elseif ($this->status == 'un_subscribe') {
            return [
                'refer_name' => 'You have performed un subscription'
            ];
        }elseif ($this->status == 'enterprise_un_subscribe') {
            return [
                'refer_name' => 'You have performed un subscription for Enterprise plan'
            ];
        }elseif ($this->status == 'resume') {
            return [
                'refer_name' => 'You have resumed the subscription'
            ];
        }elseif ($this->status == 'enterprise_resume') {
            return [
                'refer_name' => 'You have resumed the Enterprise subscription plan'
            ];
        }elseif ($this->status == 'subscription_renew_trial') {
            return [
                'subscription_renew_text' => 'Your subscription will be re new at ',
                'trial_for_text' => 'Your trial period only for ',
                'subscription_renew_date' => $this->subscription[0],
                'trial_for_date' => $this->subscription[1],
                'channel_name' => $this->channel,
            ];
        }elseif ($this->status == 'enterprise-enduser') {
            return [
                'refer_name' => 'You have performed subscription for Enterprise plan'
            ];
        }elseif ($this->status == 'enterprise-admin') {

            return [
                'refer_name' => 'User request for Enterprise plan. Check inbox'
            ];
        }elseif ($this->status == 'enterpriseSubApproval') {
            return [
                'refer_name' => 'Cyrano support have accepted your request for Enterprise plan'
            ];
        }elseif ($this->status == 'enterpriseSubUnApproval') {
            return [
                'refer_name' => 'Cyrano Support have un approved your request for enterprise plan'
            ];
        }
    }
}
