# Simple OneSignal PHP Client

```
<?php

use Mingalevme\OneSignal\Client as OneS;

$OneSignal = new OneS($oneSignalAppId, $oneSignalRestKey);

$OneSignal->send('title', [
    'type'=> 'my-notification-type',
    'data' => 'some-extra-data',
], [
    'tag1' => 'value1',
    'tag2' => 'value2',
], [
    // ...
    OneS::SMALL_ICON => 'push_icon',
    OneS::TTL => 3600*3,
    OneS::IOS_CATEGORY => 'my-ios-category',
    // ...
    OneS::TAGS => [ // This will be merged with 'tag1' => 'value1' and 'tag2' => 'value2'
        [
            'key' => 'tag3',
            'relation' => '>',
            'value' => 'value3'
        ],
    ]
    // ...
]);
```

# Testing (OSX)

## Install PHP 7.4+

```shell
brew install php@7.4
```

## Composer

```shell
brew install composer
/usr/local/opt/php@7.4/bin/php /usr/local/bin/composer install
```

## phpunit

```shell
/usr/local/opt/php@7.4/bin/php vendor/bin/phpunit
```
