<?php

use App\Services\SlackNotificationService;
use Illuminate\Support\Facades\Log;

describe('SlackNotificationService', function () {
    it('logs channel message when simulate is true', function () {
        Log::shouldReceive('channel')
            ->with('slack_simulation')
            ->once()
            ->andReturnSelf();
        Log::shouldReceive('info')
            ->once()
            ->andReturnNull();

        $service = new SlackNotificationService(simulate: true);
        $service->sendChannelMessage('#test-channel', 'Test message');
    });

    it('logs direct message when simulate is true', function () {
        Log::shouldReceive('channel')
            ->with('slack_simulation')
            ->once()
            ->andReturnSelf();
        Log::shouldReceive('info')
            ->once()
            ->andReturnNull();

        $service = new SlackNotificationService(simulate: true);
        $service->sendDirectMessage('U12345', 'Test DM');
    });
});
