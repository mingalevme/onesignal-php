<?php

declare(strict_types=1);

namespace Mingalevme\Tests\OneSignal\Suites\Feature;

use InvalidArgumentException;
use Mingalevme\OneSignal\CreateNotificationOptions;
use Mingalevme\OneSignal\Notification\PushNotification;

class RawAttributesTest extends AbstractFeatureTestCase
{
    public function testSettingRawValue(): void
    {
        $notification = $this->createNotification()
            ->setAttribute('attr1', 'value1')
            ->setAttribute('attr2', 'value2')
            ->setAttributes([
                'attr2' => 'value2-1',
                'attr3' => 'value3',
            ]);
        $attributes = [
            'attr1' => 'value1',
            'attr2' => 'value2-1',
            'attr3' => 'value3',
        ];
        self::assertNotificationHasAttributes($attributes, $notification);
    }

    public function testItShouldThrowErrorInCaseOfEmptyNotification(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Notification cannot be empty');
        PushNotification::createContentsNotification('test')
            ->setAttribute(CreateNotificationOptions::CONTENTS, null)
            ->toOneSignalData();
    }

    public function testItShouldThrowErrorInCaseOfEmptyEnData(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid or missing default text of notification (content["en"])');
        PushNotification::createContentsNotification([
            'es' => 'es',
        ])->toOneSignalData();
    }
}
