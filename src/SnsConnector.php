<?php

namespace Hobbii\PubSub;

use Aws\Sqs\SqsClient;
use Illuminate\Queue\Connectors\SqsConnector;
use Illuminate\Support\Arr;

class SnsConnector extends SqsConnector
{
    /**
     * @param mixed[] $config
     * @return SnsQueue
     */
    public function connect(array $config): SnsQueue
    {
        $config = $this->getDefaultConfiguration($config);

        if (!empty($config['key']) && !empty($config['secret'])) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }

        return new SnsQueue(
            new SqsClient($config),
            $config['queue'],
        );
    }
}
