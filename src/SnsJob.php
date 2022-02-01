<?php

namespace Hobbii\PubSub;

use Aws\Sqs\SqsClient;
use Hobbii\PubSub\Jobs\DeleteJob;
use Illuminate\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Jobs\SqsJob;
use Illuminate\Queue\ManuallyFailedException;
use Illuminate\Support\Facades\Config;

class SnsJob extends SqsJob
{
    /**
     * @param Container $container
     * @param SqsClient $sqs
     * @param mixed[] $job
     * @param string $connectionName
     * @param string $queue
     */
    public function __construct(Container $container, SqsClient $sqs, array $job, $connectionName, $queue)
    {
        $body = json_decode($job['Body'], associative: true);
        if (!isset($body['uuid'])) {
            $body['uuid'] = $job['MessageId'] ?? '';
            $job['Body'] = json_encode($body);
        }
        parent::__construct($container, $sqs, $job, $connectionName, $queue);
    }

    /**
     * @return mixed[]
     * @throws \Throwable
     */
    public function payload(): array
    {
        $payload = parent::payload();

        $message = json_decode($payload['Message'], associative: true);

        throw_unless(
            $type = $message['type'],
            \Exception::class,
            sprintf('Message with id [%s] does not have a type', $this->getJobId())
        );

        $payload['job'] = $this->mapJob($type);

        $payload['data'] = $message['data'];

        return $payload;
    }

    public function fire(): void
    {
        parent::fire();

        $this->delete();
    }

    public function fail($e = null): void
    {
        $this->markAsFailed();

        if ($this->isDeleted()) {
            return;
        }

        try {
            $this->release();

            $this->failed($e);
        } finally {
            $this->resolve(Dispatcher::class)->dispatch(new JobFailed(
                $this->connectionName,
                $this,
                $e ?: new ManuallyFailedException
            ));
        }
    }

    /**
     * @param string $type
     * @return string
     * @throws \Exception
     */
    private function mapJob(string $type): string
    {
        if ($job = Config::get('queue.connections.sns.mapping.' . $type)) {
            return $job;
        }

        if (Config::get('queue.connections.sns.delete_unmapped')) {
            return DeleteJob::class;
        }

        throw new \Exception("Type $type is not mapped to any Job");
    }
}
