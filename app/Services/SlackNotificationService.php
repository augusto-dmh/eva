<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SlackNotificationService
{
    public function __construct(private bool $simulate = false) {}

    public function sendChannelMessage(string $channel, string $message): void
    {
        if ($this->simulate) {
            Log::channel('slack_simulation')->info("CHANNEL:{$channel}", ['message' => $message]);

            return;
        }
        // Real Slack API — not wired for hackathon
    }

    public function sendDirectMessage(string $slackUserId, string $message): void
    {
        if ($this->simulate) {
            Log::channel('slack_simulation')->info("DM:{$slackUserId}", ['message' => $message]);

            return;
        }
        // Real Slack API — not wired for hackathon
    }
}
