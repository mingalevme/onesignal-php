<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal\Notification;

class WebActionButton extends ActionButton
{
    /** @var non-empty-string|null */
    protected ?string $launchUrl;

    /**
     * @param non-empty-string $actionId
     * @param non-empty-string $text
     * @param non-empty-string $icon URL or resource id
     * @param non-empty-string|null $launchUrl
     */
    public function __construct(string $actionId, string $text, string $icon, ?string $launchUrl = null)
    {
        parent::__construct($actionId, $text, $icon);
        $this->launchUrl = $launchUrl;
    }

    /**
     * @return non-empty-string|null
     */
    public function getLaunchUrl(): ?string
    {
        return $this->launchUrl;
    }

    /**
     * @return array{id: non-empty-string, text: non-empty-string, icon: non-empty-string, url: non-empty-string}
     * @psalm-suppress MoreSpecificReturnType
     */
    public function toOneSignalActionButton(): array
    {
        /** @psalm-suppress LessSpecificReturnStatement */
        return parent::toOneSignalActionButton() + [
            'url' => $this->getLaunchUrl() ?: 'do_not_open',
        ];
    }
}
