<?php

namespace Hobbii\PubSub\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Str;

abstract class AbstractSnsEvent implements ShouldBroadcast
{
    public string $type;
    /** @var mixed[] */
    public array $data;
    public string $groupId;
    public string $deduplicationId;

    public function __construct(private string $topic)
    {
        $this->deduplicationId = Str::uuid();
    }

    /**
     * @inheritDoc
     */
    public function broadcastOn(): string
    {
        return $this->topic;
    }
}
