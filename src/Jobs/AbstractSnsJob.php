<?php

namespace Hobbii\PubSub\Jobs;

use Illuminate\Queue\Jobs\Job;

abstract class AbstractSnsJob
{
    /**
     * @param mixed[] $data
     * @return void
     */
    abstract public function handle(array $data): void;

    /**
     * @param Job $job
     * @param mixed[] $data
     * @return void
     */
    public function fire(Job $job, array $data): void
    {
        $this->handle($data);
    }
}
