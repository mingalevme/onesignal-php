<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal\Notification;

class ActionButton
{
    /** @var non-empty-string */
    protected string $actionId;
    /** @var non-empty-string */
    protected string $text;
    /** @var non-empty-string */
    protected string $icon;

    /**
     * @param non-empty-string $actionId
     * @param non-empty-string $text
     * @param non-empty-string $icon URL or resource id
     */
    public function __construct(string $actionId, string $text, string $icon, ?string $launchUrl = null)
    {
        $this->actionId = $actionId;
        $this->text = $text;
        $this->icon = $icon;
    }

    /**
     * @return non-empty-string
     */
    public function getActionId(): string
    {
        return $this->actionId;
    }

    /**
     * @return non-empty-string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return non-empty-string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @return array{id: non-empty-string, text: non-empty-string, icon: non-empty-string}
     */
    public function toOneSignalActionButton(): array
    {
        return [
            'id' => $this->getActionId(),
            'text' => $this->getText(),
            'icon' => $this->getIcon(),
        ];
    }
}
