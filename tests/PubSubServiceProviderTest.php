<?php

namespace Tests;

use Hobbii\PubSub\PubSubServiceProvider;
use Hobbii\PubSub\SnsBroadcaster;
use Hobbii\PubSub\SnsQueue;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Orchestra\Testbench\TestCase;

class PubSubServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        Config::set('broadcasting.connections.sns', [
            'driver' => 'sns',
            'key' => 'mykey',
            'secret' => 'mysecret',
            'region' => 'my-region',
            'suffix' => 'my-suffix',
            'prefix' => 'my-prefix',
        ]);
        Config::set('queue.default', 'sns');
        Config::set('queue.connections.sns', [
            'driver' => 'sns',
            'key' => 'mykey',
            'secret' => 'mysecret',
            'prefix' => 'my-prefix',
            'queue' => 'my-queue',
            'suffix' => 'my-suffix',
            'region' => 'my-region',
            'after_commit' => false,
        ]);
        return [
            PubSubServiceProvider::class,
        ];
    }

    public function testBroadcastManagerIsExtended(): void
    {
        $this->assertInstanceOf(SnsBroadcaster::class, Broadcast::connection('sns'));
    }

    public function testQueueManagerIsExtended(): void
    {
        $this->assertInstanceOf(SnsQueue::class, Queue::setConnectionName('sns'));
    }
}
