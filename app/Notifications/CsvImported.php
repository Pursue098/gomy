<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use App\User;
use App\Project;
use App\Csv;

class CsvImported extends Notification
{
    use Queueable;

    private $user;
    private $project;
    private $csv;
    private $imported;
    private $errors;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, Project $project, Csv $csv, $imported, $errors)
    {
        $this->user     = $user;
        $this->project  = $project;
        $this->csv      = $csv;
        $this->imported = $imported;
        $this->errors   = $errors;
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
        $msg = (new MailMessage)
                    ->subject('CSV imported')
                    ->greeting('Hello ' . $this->user->name . '!')
                    ->line($this->imported . ' lines from file ' . $this->csv->name . ' have been imported.');

        if ($this->errors > 0) {
            $msg->action('Skipped ' . $this->errors . ' rows because of errors.', route('crm.csv.errors', [$this->project, $this->csv]));
        }

        return $msg->line('Thank you for using Cyrano!');
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
            'user_id'    => $this->user->id,
            'user_name'  => $this->user->name,
            'project_id' => $this->project->id,
            'csv'        => $this->csv,
            'imported'   => $this->imported,
            'errors'     => $this->errors,
        ];
    }
}
