<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal\Notification;

class SmsNotification extends AbstractNotification
{
    use SmsNotificationChannelPropertiesTrait;

    /**
     * @param non-empty-string $name
     * @param non-empty-string|non-empty-array<non-empty-string, non-empty-string> $contents
     * @return self
     */
    public static function createNotification(string $name, $contents): self
    {
        return self::new()
            ->setName($name)
            ->setContents($contents);
    }

    protected static function new(): self
    {
        return new self();
    }

    protected function __construct()
    {
    }
}
