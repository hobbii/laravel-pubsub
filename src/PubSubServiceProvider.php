<?php

namespace Hobbii\PubSub;

use Aws\Sns\SnsClient;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;

class PubSubServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app
            ->make(BroadcastManager::class)
            ->extend(
                'sns',
                fn ($app, $config) => new SnsBroadcaster(
                    new SnsClient([
                        'credentials' => [
                            'key' => $config['key'],
                            'secret' => $config['secret'],
                        ],
                        'region' => $config['region'],
                        'version' => 'latest',
                    ]),
                    $config['prefix'],
                    $config['suffix']
                )
            );

        $this->app
            ->make(QueueManager::class)
            ->addConnector('sns', fn () => new SnsConnector());
    }
}
