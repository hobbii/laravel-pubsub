<?php

namespace Hobbii\PubSub;

use Aws\Sns\SnsClient;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;

class PubSubServiceProvider extends ServiceProvider
{
    /**
     * @return void
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->extendBroadcastManager();
        $this->extractQueueManager();
    }

    /**
     * @return void
     * @throws BindingResolutionException
     */
    private function extendBroadcastManager(): void
    {
        $this->app
            ->make(BroadcastManager::class)
            ->extend(
                'pubsub',
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
    }

    /**
     * @return void
     * @throws BindingResolutionException
     */
    private function extractQueueManager(): void
    {
        $this->app
            ->make(QueueManager::class)
            ->addConnector('pubsub', fn () => new SnsConnector());
    }
}
