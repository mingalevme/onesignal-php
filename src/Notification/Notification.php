<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal\Notification;

/**
 * @readonly
 */
final class Notification implements NotificationInterface
{
    /** @var non-empty-array<string, mixed> */
    private array $oneSignalData;

    /**
     * @param non-empty-array<string, mixed> $oneSignalData
     */
    public function __construct(array $oneSignalData)
    {
        $this->oneSignalData = $oneSignalData;
    }

    /**
     * @return non-empty-array<string, mixed>
     */
    public function toOneSignalData(): array
    {
        return $this->oneSignalData;
    }
}
