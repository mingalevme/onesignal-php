<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal\Notification;

use InvalidArgumentException;
use Mingalevme\OneSignal\CreateNotificationOptions as CNO;

/**
 * https://documentation.onesignal.com/reference/push-channel-properties
 */
final class PushNotification extends AbstractNotification
{
    use PushNotificationChannelPropertiesTrait;

    /**
     * @param non-empty-string|non-empty-array<non-empty-string, non-empty-string> $contents
     * @return self
     */
    public static function createContentsNotification($contents): self
    {
        return self::new()->setContents($contents);
    }

    /**
     * https://documentation.onesignal.com/docs/data-notifications
     *
     * @param non-empty-array<non-empty-string, mixed> $data
     * @return self
     */
    public static function createContentAvailableNotification(?array $data = null): self
    {
        $notification = self::new()
            ->setContentAvailable(true);
        if ($data) {
            $notification->setData($data);
        }
        return $notification;
    }

    /**
     * Use a template you set up on our dashboard.
     *
     * The template_id is the UUID found in the URL when viewing a template on our dashboard.
     *
     * Example: be4a8044-bbd6-11e4-a581-000c2940e62c
     *
     * @param non-empty-string $templateId
     * @return self
     */
    public static function createTemplateNotification(string $templateId): self
    {
        return self::new()->setTemplateId($templateId);
    }

    protected static function new(): self
    {
        return new self();
    }

    protected function __construct()
    {
    }

    public function toOneSignalData(): array
    {
        $data = parent::toOneSignalData();

        if (!empty($data[CNO::DELIVERY_TIME_OF_DAY])) {
            if (($data[CNO::DELAYED_OPTION] ?? null) !== CNO::DELAYED_OPTION_TIMEZONE) {
                throw new InvalidArgumentException(
                    'delivery_time_of_day mist be used with delayed_option=timezone'
                );
            }
        }

        return $data;
    }
}
