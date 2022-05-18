<?php

namespace Tests;

use Aws\Result;
use Aws\Sqs\SqsClient;
use Hobbii\PubSub\Jobs\DeleteJob;
use Hobbii\PubSub\SnsJob;
use Hobbii\PubSub\SnsQueue;
use Illuminate\Queue\Jobs\SqsJob;
use Illuminate\Support\Facades\Config;
use Mockery\MockInterface;

class SqsQueueTest extends SqsTestCase
{
    public function testPopQueueReturnsSingleMessageAsSnsJob(): void
    {
        Config::set('queue.connections.pubsub.delete_unmapped', true);
        $expectedJob = $this->makeJob([
            'type' => 'event_created',
            'data' => [
                'event' => 'payload',
            ],
        ]);
        /** @var SqsClient $sqsClient */
        $sqsClient = $this->mock(SqsClient::class, function (MockInterface $mock) use ($expectedJob) {
            $mock->shouldReceive('receiveMessage')
                ->with([
                    'QueueUrl' => 'https://sqs.us-central-1.amazonaws.com/123456789000/MyQueue.fifo',
                    'AttributeNames' => ['ApproximateReceiveCount'],
                ])
                ->andReturn(new Result([
                    'Messages' => [
                        $expectedJob,
                        $this->makeJob([
                            'type' => 'event_updated',
                            'data' => [
                                'event' => 'payload',
                            ],
                        ]),
                    ],
                ]));
        });

        $queue = new SnsQueue(
            $sqsClient,
            'MyQueue',
            'https://sqs.us-central-1.amazonaws.com/123456789000',
            '.fifo'
        );
        $queue->setContainer($this->app);

        $job = $queue->pop();

        $this->assertInstanceOf(SnsJob::class, $job);
        $payload = $job->payload();
        $this->assertEquals(DeleteJob::class, $payload['job']);
        $this->assertEquals($expectedJob['MessageId'], $payload['uuid']);
    }

    public function testDispatchedJobReturnedAsSqsJob(): void
    {
        $expectedJob = $this->makeSqsJob('App\Jobs\MyJob');
        /** @var SqsClient $sqsClient */
        $sqsClient = $this->mock(SqsClient::class, function (MockInterface $mock) use ($expectedJob) {
            $mock->shouldReceive('receiveMessage')
                ->with([
                    'QueueUrl' => 'https://sqs.us-central-1.amazonaws.com/123456789000/MyQueue.fifo',
                    'AttributeNames' => ['ApproximateReceiveCount'],
                ])
                ->andReturn(new Result([
                    'Messages' => [
                        $expectedJob,
                    ],
                ]));
        });

        $queue = new SnsQueue(
            $sqsClient,
            'MyQueue',
            'https://sqs.us-central-1.amazonaws.com/123456789000',
            '.fifo'
        );
        $queue->setContainer($this->app);

        $job = $queue->pop();

        $this->assertEquals(SqsJob::class, get_class($job));
        $payload = $job->payload();
        $this->assertEquals('Illuminate\Queue\CallQueuedHandler@call', $payload['job']);
    }

    public function testNoMessagesReturnsNull(): void
    {
        /** @var SqsClient $sqsClient */
        $sqsClient = $this->mock(SqsClient::class, function (MockInterface $mock) {
            $mock->shouldReceive('receiveMessage')
                ->with([
                    'QueueUrl' => 'https://sqs.us-central-1.amazonaws.com/123456789000/MyQueue.fifo',
                    'AttributeNames' => ['ApproximateReceiveCount'],
                ])
                ->andReturn(new Result());
        });

        $queue = new SnsQueue(
            $sqsClient,
            'MyQueue',
            'https://sqs.us-central-1.amazonaws.com/123456789000',
            '.fifo'
        );

        $this->assertNull($queue->pop());
    }
}
