<?php

declare(strict_types=1);

namespace Mingalevme\Tests\OneSignal\Suites\Feature;

use Mingalevme\OneSignal\Notification\AbstractNotification;
use Mingalevme\OneSignal\Notification\PushNotification;
use Mingalevme\Tests\OneSignal\TestCase;

abstract class AbstractFeatureTestCase extends TestCase
{
    protected function createNotification(): AbstractNotification
    {
        return PushNotification::createContentAvailableNotification();
    }

    /**
     * @param non-empty-array<non-empty-string, mixed> $attributes
     * @param non-empty-array<non-empty-string, mixed> $oneSignalData
     * @param bool|null $strict
     * @return void
     */
    protected static function assertOneSignalDataHasAttributes(
        array $attributes,
        array $oneSignalData,
        ?bool $strict = false
    ): void {
        if ($strict) {
            self::assertCount(count($attributes), $oneSignalData);
        }
        /** @psalm-suppress MixedAssignment */
        foreach ($attributes as $name => $value) {
            self::assertSame($value, $oneSignalData[$name], $name);
        }
    }

    /**
     * @param non-empty-array<non-empty-string, mixed> $attributes
     * @param AbstractNotification $notification
     * @param bool|null $strict
     * @return void
     */
    protected static function assertNotificationHasAttributes(
        array $attributes,
        AbstractNotification $notification,
        ?bool $strict = false
    ): void {
        static::assertOneSignalDataHasAttributes($attributes, $notification->toOneSignalData(), $strict);
    }
}
