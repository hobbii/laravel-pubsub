<?php

namespace Hobbii\PubSub;

use Illuminate\Queue\Jobs\Job;
use Illuminate\Queue\Jobs\SqsJob;
use Illuminate\Queue\SqsQueue;

class SnsQueue extends SqsQueue
{
    public function pop($queue = null): ?Job
    {
        $response = $this->sqs->receiveMessage([
            'QueueUrl' => $queue = $this->getQueue($queue),
            'AttributeNames' => ['ApproximateReceiveCount'],
        ]);

        if (!is_null($response['Messages']) && count($response['Messages']) > 0) {
            $message = $response['Messages'][0];
            $body = json_decode($message['Body'], true);
            if (json_last_error() === JSON_ERROR_NONE && isset($body['job'])) {
                return new SqsJob(
                    $this->container,
                    $this->sqs,
                    $message,
                    $this->connectionName,
                    $queue
                );
            }

            return new SnsJob(
                $this->container,
                $this->sqs,
                $response['Messages'][0],
                $this->connectionName,
                $queue
            );
        }

        return null;
    }
}
