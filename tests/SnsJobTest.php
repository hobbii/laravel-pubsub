<?php

namespace Tests;

use Aws\Sqs\SqsClient;
use Hobbii\PubSub\Jobs\AbstractSnsJob;
use Hobbii\PubSub\Jobs\DeleteJob;
use Hobbii\PubSub\SnsJob;
use Illuminate\Support\Facades\Config;
use Mockery\MockInterface;

class SnsJobTest extends SqsTestCase
{
    public function testMessageTypeIsMappedToJob()
    {
        Config::set('queue.connections.pubsub.mapping', [
            'event_created' => MyEventJob::class,
        ]);
        $message = [
            'type' => 'event_created',
            'data' => [
                'title' => 'My Event',
                'payload' => [
                    'event' => 'payload',
                ],
            ],
        ];
        $job = $this->makeJob($message);

        $snsJob = new SnsJob(
            $this->app,
            $this->mock(SqsClient::class),
            $job,
            'pubsub',
            'MyQueue'
        );

        $this->assertEquals(array_merge(json_decode($job['Body'], associative: true), [
            'uuid' => $job['MessageId'],
            'job' => MyEventJob::class,
            'data' => $message['data'],
        ]), $snsJob->payload());
    }

    public function testUnmappedMessageTypeIsDefaultingToDeleteJob(): void
    {
        Config::set('queue.connections.pubsub.delete_unmapped', true);

        $snsJob = new SnsJob(
            $this->app,
            $this->mock(SqsClient::class),
            $this->makeJob(['type' => 'random_type', 'data' => ['example' => 'data']]),
            'pubsub',
            'MyQueue'
        );

        $this->assertEquals(DeleteJob::class, $snsJob->payload()['job']);
    }

    public function testUnmappedMessageTypeThrowsException(): void
    {
        Config::set('queue.connections.pubsub.deleted_unmapped', false);

        $snsJob = new SnsJob(
            $this->app,
            $this->mock(SqsClient::class),
            $this->makeJob(['type' => 'throw_exception', 'data' => ['example' => 'data']]),
            'pubsub',
            'MyQueue'
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Type throw_exception is not mapped to any Job');

        $snsJob->payload();
    }

    public function testExecutingJobDeletesFromSQSOnSuccess(): void
    {
        Config::set('queue.connections.pubsub.mapping', [
            'event_created' => MyEventJob::class,
        ]);
        $message = [
            'type' => 'event_created',
            'data' => [
                'title' => 'My Event',
                'payload' => [
                    'event' => 'payload',
                ],
            ],
        ];
        $job = $this->makeJob($message);

        $sqsClient = $this->mock(SqsClient::class, function (MockInterface $mock) use ($job) {
            $mock->shouldReceive('deleteMessage')
                ->with([
                    'QueueUrl' => 'MyQueue',
                    'ReceiptHandle' => $job['ReceiptHandle'],
                ]);
        });

        $snsJob = new SnsJob(
            $this->app,
            $sqsClient,
            $job,
            'pubsub',
            'MyQueue'
        );

        $snsJob->fire();
    }

    public function testFailingJobShouldBeReleasedNotDeletedFromSQS(): void
    {
        Config::set('queue.connections.pubsub.mapping', [
            'event_created' => MyEventJob::class,
        ]);
        $message = [
            'type' => 'event_created',
            'data' => [
                'title' => 'My Event',
                'payload' => [
                    'event' => 'payload',
                ],
            ],
        ];
        $job = $this->makeJob($message);

        $sqsClient = $this->mock(SqsClient::class, function (MockInterface $mock) use ($job) {
            $mock->shouldReceive('changeMessageVisibility')
                ->with([
                    'QueueUrl' => 'MyQueue',
                    'ReceiptHandle' => $job['ReceiptHandle'],
                    'VisibilityTimeout' => 0,
                ]);
            $mock->shouldNotReceive('deleteMessage');
        });

        $snsJob = new SnsJob(
            $this->app,
            $sqsClient,
            $job,
            'pubsub',
            'MyQueue'
        );

        $snsJob->fail();
    }

    public function testDeletedJobDoesNothingWhenFailed(): void
    {
        $sqsClient = $this->mock(SqsClient::class, function (MockInterface $mock) {
            $mock->shouldReceive('deleteMessage');
        });

        $snsJob = new SnsJob(
            $this->app,
            $sqsClient,
            $this->makeJob([]),
            'sns',
            'MyQueue'
        );

        $snsJob->delete();

        $snsJob->fail();
    }
}

class MyEventJob extends AbstractSnsJob
{
    public function handle(array $data): void
    {
    }
}
