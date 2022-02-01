<?php

namespace Tests\Events;

use Faker\Factory;
use Hobbii\PubSub\Events\AbstractSnsEvent;
use PHPUnit\Framework\TestCase;

class AbstractSnsEventTest extends TestCase
{
    public function testAbstractSnsEventSetsProperties()
    {
        $faker = Factory::create();
        $topic = $faker->word;
        $snsEvent = new Event($topic);

        $this->assertEquals($topic, $snsEvent->broadcastOn());
        $this->assertMatchesRegularExpression(
            '/^[a-f0-9]{8}-([a-f0-9]{4}-){3}[a-f0-9]{12}$/',
            $snsEvent->deduplicationId
        );
    }
}

class Event extends AbstractSnsEvent
{

}
