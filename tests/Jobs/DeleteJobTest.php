<?php

namespace Tests\Jobs;

use Hobbii\PubSub\Jobs\DeleteJob;
use Illuminate\Queue\Jobs\SyncJob;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase;

class DeleteJobTest extends TestCase
{
    public function testDeleteJobTestIsFirable(): void
    {
        $job = $this->partialMock(DeleteJob::class, function (MockInterface $mock) {
            $mock->shouldReceive('handle')
                ->with([
                    'data' => 'payload',
                ]);
        });

        $job->fire(new SyncJob(
            $this->app,
            'payload',
            'sync',
            'default'
        ), [
            'data' => 'payload',
        ]);
    }
}
