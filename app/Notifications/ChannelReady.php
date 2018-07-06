<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Project;
use App\Channel;

class ChannelReady extends Notification implements ShouldQueue
{
    use Queueable;

    private $project;
    private $channel;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Project $project, Channel $channel)
    {
        $this->project = $project;
        $this->channel = $channel;
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
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // Se vogliamo possiamo inviare una mail quando il canale è pronto
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
            'project_id'   => $this->project->id,
            'project_name' => $this->project->name,
            'channel_id'   => $this->channel->id,
            'channel_type' => $this->channel->type,
            'channel_name' => $this->channel->name,
        ];
    }
}
