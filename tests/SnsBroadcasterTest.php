<?php

namespace Tests;

use Aws\Sns\SnsClient;
use Faker\Factory;
use Faker\Generator;
use Hobbii\PubSub\SnsBroadcaster;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase;

class SnsBroadcasterTest extends TestCase
{
    private Generator $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testCanBroadcastEvent(): void
    {
        $payload = [
            'type' => 'event_test',
            'data' => [
                'this' => 'is',
                'my' => 'test'
            ]
        ];
        $deduplicationId = $this->faker->uuid;
        $messageGroupId = 'event_test_1';
        $snsClientMock = $this->mock(SnsClient::class, function (MockInterface $mock) use ($payload, $deduplicationId, $messageGroupId) {
            $mock->shouldReceive('publish')
                ->with([
                    'TopicArn' => 'arn:aws:sns:us-central-1:123456789:MyTopic.fifo',
                    'Message' => json_encode($payload),
                    'MessageDeduplicationId' => $deduplicationId,
                    'MessageGroupId' => $messageGroupId
                ]);
        });

        $broadcaster = new SnsBroadcaster(
            $snsClientMock,
            'arn:aws:sns:us-central-1:123456789:',
            '.fifo'
        );

        $payload['groupId'] = $messageGroupId;
        $payload['deduplicationId'] = $deduplicationId;

        $broadcaster->broadcast(['MyTopic'], 'EventClass', $payload);
    }

    public function testThrowsExceptionWhenFifoEventHasNoGroupId(): void
    {
        $payload = [
            'type' => 'event_test',
            'data' => [
                'this' => 'is',
                'my' => 'test'
            ],
            'deduplicationId' => $this->faker->uuid
        ];

        $broadcaster = new SnsBroadcaster(
            $this->mock(SnsClient::class),
            'arn',
            '.fifo'
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('A FIFO topic requires SNSEvents to have the groupId property!');

        $broadcaster->broadcast(['MyTopic'], 'EventClass', $payload);
    }

    public function testAuthAndValidateAlwaysReturnsTrue(): void
    {
        $broadcaster = new SnsBroadcaster(
            $this->mock(SnsClient::class),
            'arn',
            'suffix'
        );

        $this->assertTrue($broadcaster->auth(new Request()));
        $this->assertTrue($broadcaster->validAuthenticationResponse(new Request(), 'result'));
    }
}
