<?php

namespace Tests;

use Faker\Factory;
use Faker\Generator;
use Orchestra\Testbench\TestCase;

class SqsTestCase extends TestCase
{
    protected Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }
    protected function makeJob(array $message)
    {
        $arn = 'arn:aws:sns:us-central-1:123456789000:MyTopic.fifo';

        $body = json_encode([
            'Type' => 'Notification',
            'MessageId' => $this->faker->uuid,
            'SequenceNumber' => '10000000000000090000',
            'TopicArn' => $arn,
            'Message' => json_encode($message),
            'Timestamp' => now()->toISOString(),
            'UnsubscribeURL' => "https://sns.us-central-1.amazonaws.com/?Action=Unsubscribe&SubscriptionArn=$arn:{$this->faker->uuid}"
        ], JSON_PRETTY_PRINT);

        return [
            'MessageId' => $this->faker->uuid,
            'ReceiptHandle' => base64_encode(random_bytes(256)),
            'MD5OfBody' => hash('md5', $body),
            'Body' => $body,
            'Attributes' => [
                'ApproximateReceiveCount' => 1,
            ],
        ];
    }
}
