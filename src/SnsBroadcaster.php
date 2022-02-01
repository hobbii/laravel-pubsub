<?php

namespace Hobbii\PubSub;

use Aws\Sns\SnsClient;
use Illuminate\Contracts\Broadcasting\Broadcaster;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SnsBroadcaster implements Broadcaster
{
    public function __construct(
        protected SnsClient $client,
        protected string $arn,
        protected string $suffix,
    ) {
    }

    /**
     * @param string[] $channels
     * @param string $event
     * @param mixed[] $payload
     * @return void
     * @throws \Throwable
     */
    public function broadcast(array $channels, $event, array $payload = []): void
    {
        $data = [
            'TopicArn' => $this->topicArn(Arr::first($channels)),
            'Message' => json_encode(Arr::except($payload, ['socket', 'groupId', 'deduplicationId'])),
            'MessageDeduplicationId' => Arr::get($payload, 'deduplicationId'),
        ];

        if ($groupId = Arr::get($payload, 'groupId')) {
            $data['MessageGroupId'] = $groupId;
        }

        throw_if(
            $this->isFifoTopic() && !Arr::has($data, 'MessageGroupId'),
            \RuntimeException::class,
            'A FIFO topic requires SNSEvents to have the groupId property!',
        );

        $this->client->publish($data);
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function auth($request): bool
    {
        return true;
    }

    /**
     * @param Request $request
     * @param mixed $result
     * @return bool
     */
    public function validAuthenticationResponse($request, $result): bool
    {
        return true;
    }

    /**
     * @param string $channel
     * @return string
     */
    private function topicArn(string $channel): string
    {
        return $this->arn . $channel . $this->suffix;
    }

    /**
     * @return bool
     */
    private function isFifoTopic(): bool
    {
        return Str::contains($this->suffix, '.fifo');
    }
}
