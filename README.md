# Simple OneSignal PHP Client

![quality](https://github.com/mingalevme/onesignal/actions/workflows/quality.yml/badge.svg)
[![codecov](https://codecov.io/gh/mingalevme/onesignal/branch/feature/v2/graph/badge.svg?token=JelfrDfOkJ)](https://codecov.io/gh/mingalevme/onesignal)

## Examples

### Example (DI Container)

Set up some DI container somewhere like Application-level service provider:

```php
<?php

declare(strict_types=1);

use Mingalevme\OneSignal\ClientFactory;
use Mingalevme\OneSignal\ClientFactoryInterface;
use Mingalevme\OneSignal\ClientInterface;
use Mingalevme\OneSignal\CreateNotificationOptions;

class AppServiceProvider
{
    public function register(SomeConfigClass $someConfig, SomeDIContainerClass $container): void
    {
        // ...
        /** @var string $appId */
        $appId = $someConfig->get('onesignal-app-id');
        /** @var string $restApiKey */
        $restApiKey = $someConfig->get('my-rest-api-key');

        $container->set(ClientFactoryInterface::class, ClientFactory::class);
        $container->set(
            ClientInterface::class,
            fn() => $container->get(ClientFactoryInterface::class)->create($appId, $restAPIKey)
        );
        // ...
    }
}
```

Some application logic

```php
<?php

declare(strict_types=1);

use Mingalevme\OneSignal\ClientFactory;
use Mingalevme\OneSignal\ClientFactoryInterface;
use Mingalevme\OneSignal\ClientInterface;
use Mingalevme\OneSignal\CreateNotificationOptions;

class NotifySubscribersOnPostCreatedEventHandler
{
    private SubscribersRespo $repo;
    private ClientInterface $client;

    public function __construct(SubscribersRepo $repo, ClientInterface $client)
    {
        $this->repo = $repo;
        $this->client = $client;
    }

    public function handle(PostCreatedEvent $event): void
    {
        $post = $event->getPost();
        /** @var string[] $subscriberIds */
        $subscriberIds = $this->repo->getSubscriberIdsOfAuthor($post->getAuthorId());
        $this->client->createNotification($post->getTitle(), [
            'post_id' => $post->getId(),
        ], null, [
            CreateNotificationOptions::INCLUDE_EXTERNAL_USER_IDS => $subscriberIds,
            CreateNotificationOptions::HEADINGS => [
                'en' => $post->getAuthorDisplayName(),
            ],
            CreateNotificationOptions::EXTERNAL_ID => "post-{$post->getId()}",
        ]);
    }
}
```

### Example (Inline)

```php
<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\HttpFactory;
use Mingalevme\OneSignal\Client;
use Mingalevme\OneSignal\CreateNotificationOptions;
use Psr\Log\NullLogger;

$appId = 'my-app-id';
$restApiKey = 'my-rest-api-key';

$psrHttpClient = new \GuzzleHttp\Client();
$psr7Factory = new HttpFactory();
$logger = new NullLogger();

$client = new Client($appId, $restApiKey, $psrHttpClient, $psr7Factory, $psr7Factory, $logger);
$result = $client->createNotification('text', [
    'type' => 'my-notification-type',
    'data' => 'some-extra-data',
], [
    'tag1' => 'value1',
    'tag2' => 'value2',
], [
    // ...
    CreateNotificationOptions::SMALL_ICON => 'push_icon',
    CreateNotificationOptions::TTL => 3600 * 3,
    CreateNotificationOptions::IOS_CATEGORY => 'my-ios-category',
    CreateNotificationOptions::EXTERNAL_ID => 'custom-notification-id',
    // ...
    CreateNotificationOptions::TAGS => [ // This will be merged with 'tag1' => 'value1' and 'tag2' => 'value2'
        [
            'key' => 'tag3',
            'relation' => '>',
            'value' => 'value3',
        ],
    ]
    // ...
]);

echo <<<END
Notification has been successfully sent to OneSignal: #{$result->getNotificationId()}.
Total recipients: {$result->getTotalNumberOfRecipients()}.
END;
```

## Quality (OSX)

### Install PHP 7.4+

```shell
brew install php@7.4
/usr/local/opt/php@7.4/bin/pecl install xdebug
```

### Composer

```shell
brew install composer
/usr/local/opt/php@7.4/bin/php /usr/local/bin/composer install
```

### PHPUnit

```shell
/usr/local/opt/php@7.4/bin/php vendor/bin/phpunit
```

### Psalm

```shell
/usr/local/opt/php@7.4/bin/php vendor/bin/psalm
```

### PHPStan

```shell
/usr/local/opt/php@7.4/bin/php vendor/bin/phpstan --xdebug analyse
```

### PHP_CodeSniffer

```shell
/usr/local/opt/php@7.4/bin/php vendor/bin/phpstan --xdebug analyse
```
