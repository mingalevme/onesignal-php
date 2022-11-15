<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal\Notification;

final class EmailNotification extends AbstractNotification
{
    use EmailNotificationChannelPropertiesTrait;

    /**
     * @param non-empty-string $subject
     * @param non-empty-string $body HTML supported $body
     * @return static
     */
    public static function createBodyNotification(string $subject, string $body): self
    {
        return self::new()
            ->setEmailSubject($subject)
            ->setEmailBody($body);
    }

    /**
     * @param non-empty-string $subject
     * @param non-empty-string $templateId UUID
     * @return static
     */
    public static function createTemplateNotification(string $subject, string $templateId): self
    {
        return self::new()
            ->setEmailSubject($subject)
            ->setTemplateId($templateId);
    }

    protected static function new(): self
    {
        return new self();
    }

    protected function __construct()
    {
    }
}
