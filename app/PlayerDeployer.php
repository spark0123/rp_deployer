<?php

namespace App;

use Illuminate\Notifications\Notifiable;

class PlayerDeployer
{
    use Notifiable;
    private $slack_webhook_url = env('slack_webhook_url', '');

    /**
     * Route notifications for the Slack channel.
     *
     * @return string
     */
    public function routeNotificationForSlack()
    {
        return $this->slack_webhook_url;
    }
}
