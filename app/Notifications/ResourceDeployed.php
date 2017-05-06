<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;

class ResourceDeployed extends Notification
{
    use Queueable;
    private $repo;
    private $branch;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($repo, $branch)
    {
        //
        $this->repo = $repo;
        $this->branch = $branch;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

     /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable)
    {
        $repo = $this->repo;
        $branch = $this->branch;
        return (new SlackMessage)
            ->content('git repo = '. $repo . ', branch = ' . $branch . ' has been deployed.');
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
