# Hobbii/Laravel-PubSub
[![codecov](https://codecov.io/gh/hobbii/laravel-pubsub/branch/main/graph/badge.svg?token=9I6H1sxORL)](https://codecov.io/gh/hobbii/laravel-pubsub)
![CI Workflow](https://github.com/hobbii/laravel-pubsub/actions/workflows/ci.yml/badge.svg?branch=main)

A Pub/Sub package for Laravel using AWS [SQS](https://aws.amazon.com/sqs/) and [SNS](https://aws.amazon.com/sns/)

```shell
composer require hobbii/laravel-pubsub
```

## Installation
Update `config/broadcasting.php`, adding an `pubsub` connection:
```php
<?php

return [
    ...
    'connections' => [
        ...
        'pubsub' => [
            'driver' => 'pubsub',
            'key' => env('AWS_SNS_KEY_ID'),
            'secret' => env('AWS_SNS_ACCESS_KEY'),
            'region' => env('AWS_SNS_REGION'),
            'suffix' => env('AWS_SNS_TOPIC_SUFFIX'),
            'prefix' => env('AWS_SNS_TOPIC_PREFIX'),
        ]
    ]
];
```

Update `config/queues.php`, adding an `pubsub` connection:
```php
<?php

return [
    ...
    'connections' => [
        ...
        'pubsub' => [
            'driver' => 'pubsub',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue' => env('SQS_QUEUE', 'default'),
            'suffix' => env('SQS_SUFFIX'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'after_commit' => false,
            'mapping' => [
            ],
            'delete_unmapped' => false,
        ],
    ]
];
```


## Usage
### Publishing Events to SNS
To publish an event to SNS, an SnsEvent must be created:

```php
<?php
namespace App\Events;

use Hobbii\PubSub\Events\AbstractSnsEvent;
use Illuminate\Support\Str;

class MyEvent extends AbstractSnsEvent
{
    // Required - this type is what the subscribers will map to a job
    public string $type = 'my_event';
    
    public function __construct()
    {
        // SNS Topic to broadcast the event on.
        parent::__construct('MyTopic');
        
        // Group ID is required for `FIFO`-topics
        $this->groupId = 'my-event';
        
        // Deduplication ID is required,
        // if your SNS Topic is not set up to
        // deduplicate messages based on content. 
        $this->deduplicationId = Str::uuid();
        
        // The payload/data/body of the message.
        $this->data = [
            'message' => 'My Event',
            'payload' => [
                'Whatever payload',
                'The subscribers should receive'
            ],
        ];
    }
}
```

Then, to broadcast the event, call
```php
broadcast(new MyEvent());
```
or, if you added the trait `Dispatchable` to your event, you can:
```php
MyEvent::dispatch();
```

### Subscribing to Events from SQS
Create a job extending from `Hobbii\PubSub\Jobs\AbstractSnsJob`.
The created job should contain a handle method, that accepts an array,
which will be the published events payload.
```php
<?php

namespace App\Jobs;

use Hobbii\PubSub\Jobs\AbstractSnsJob;

class MyJob extends AbstractSnsJob
{
    public function handle(array $data): void
    {
        // Handle the incoming payload data here.
    }
}
```
In `config/queue.php` under `connections.pubsub.mapping`, map the event types to the jobs:
```php
    'pubsub' => [
        'driver' => 'pubsub',
        ...
        'mapping' => [
            'my_event' => \App\Jobs\MyJob::class,
        ],
    ]
```
Start the queue worker to listen for events on sqs, via:
```shell
php artisan queue:work pubsub
```

## Testing
You can find tests in the `/tests` folder, and you can run them by using `./vendor/bin/phpunit`.

## Static analysis
You can run [PHPStan](https://phpstan.org/), by executing `./vendor/bin/phpstan analyse`

## Contributing
See how to contribute in [CONTRIBUTING.md](CONTRIBUTING.md)

## Code of Conduct
Hobbii/Laravel-PubSub has adopted a [Code of Conduct](CODE_OF_CONDUCT.md) that we expect project participants to adhere to.
Please read the full text so that you can understand what actions will and will not be tolerated.

## License
MIT
