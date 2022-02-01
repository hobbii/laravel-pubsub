<?php

namespace Hobbii\PubSub\Jobs;

class DeleteJob extends AbstractSnsJob
{
    /**
     * @inheritDoc
     */
    public function handle(array $data): void
    {
        // Do nothing, let the job complete and be deleted from the Queue.
    }
}
