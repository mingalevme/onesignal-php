# Simple OneSignal PHP Client

[![quality](https://github.com/mingalevme/onesignal-php/actions/workflows/quality.yml/badge.svg)](https://github.com/mingalevme/onesignal-php/actions)
[![codecov](https://codecov.io/gh/mingalevme/onesignal-php/branch/master/graph/badge.svg?token=JelfrDfOkJ)](https://codecov.io/gh/mingalevme/onesignal-php)
[![version](https://img.shields.io/packagist/v/mingalevme/onesignal?include_prereleases)](https://packagist.org/packages/mingalevme/onesignal)
[![license](https://img.shields.io/packagist/l/mingalevme/onesignal)](https://packagist.org/packages/mingalevme/onesignal)

Really simple and well tested OneSignal client (creating notifications only) with PSR-only dependencies:
- PHP 7.4+
- PSR-17: HTTP Factories
- PSR-18: HTTP Client
- JSON-extension (as of PHP 8.0.0, the JSON-extension is a core PHP extension, so it is always available)

More about code quality:
- **95%+** of code are covered with tests
- project uses **Psalm** and **PHPStan** at **max** levels 
- `composer audit` on every commit by CI/CD
- **PHP_CodeSniffer**

## Composer

```shell
composer require mingalevme/onesignal:^2.0.0-alpha
```

## Examples

### Example (DI Container)

Set up some DI container somewhere like Application-level service provider:

```php
<?php

declare(strict_types=1);

use Mingalevme\OneSignal\ClientFactory;
use Mingalevme\OneSignal\ClientFactoryInterface;
use Mingalevme\OneSignal\ClientInterface;
use Mingalevme\OneSignal\CreateClientOptions;

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
            fn() => $container->get(ClientFactoryInterface::class)
                ->create(CreateClientOptions::new($appId, $restAPIKey))
        );
        // ...
    }
}
```

Some application logic

> **Note**
> It's recommended to use some app-level interface for notification sender, e.g. `MyAppNotificationSenderInterface`,
> instead of `ClientInterface`/`CreateNotificationInterface`. 

```php
<?php

declare(strict_types=1);

use Mingalevme\OneSignal\ClientInterface;
use Mingalevme\OneSignal\CreateNotificationOptions;
use Mingalevme\OneSignal\Notification\PushNotification;

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
        $notification = PushNotification::createContentsNotification($post->getTitle())
            ->setHeadings($post->getAuthorDisplayName())
            ->setData([
                'post_id' => $post->getId(),
            ])
            ->setExternalId("post-{$post->getId()}")
            ->setIncludeExternalUserIds($subscriberIds);
        $this->client->createNotification($notification);
    }
}
```

### Example (Inline)

```php
<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\HttpFactory;
use Mingalevme\OneSignal\Client;
use Mingalevme\OneSignal\CreateClientOptions;
use Mingalevme\OneSignal\CreateNotificationOptions;
use Mingalevme\OneSignal\Notification\PushNotification;

$appId = 'my-app-id';
$restApiKey = 'my-rest-api-key';

$psrHttpClient = new \GuzzleHttp\Client();
$psr7Factory = new HttpFactory();

$client = new Client(CreateClientOptions::new($appId, $restAPIKey), $psrHttpClient, $psr7Factory, $psr7Factory);
$notification = PushNotification::createContentsNotification('text')
    ->setData([
        'type' => 'my-notification-type',
        'data' => 'some-extra-data',
    ])
    ->addFilterTagExists('tag1')
    ->addFilterTag('tag2', '>', 'value2')
    ->setSmallIcon('push_icon')
    ->setTtl(3600 * 3)
    ->setIosCategory('my-ios-category')
    ->setExternalId('custom-notification-id');
$result = $client->createNotification($notification);

echo <<<END
Notification has been successfully sent to OneSignal: #{$result->getNotificationId()}.
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
/usr/local/opt/php@7.4/bin/php vendor/bin/phpstan analyse
```

### PHP_CodeSniffer

```shell
/usr/local/opt/php@7.4/bin/php vendor/bin/phpcs
```
