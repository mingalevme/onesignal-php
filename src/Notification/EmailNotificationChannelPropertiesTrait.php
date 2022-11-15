<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal\Notification;

use Mingalevme\OneSignal\CreateNotificationOptions as CNO;

/**
 * @mixin AbstractNotification
 */
trait EmailNotificationChannelPropertiesTrait
{
    /**
     * Required: The subject of the email.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setEmailSubject(string $value): self
    {
        return $this->setAttribute(CNO::EMAIL_SUBJECT, $value);
    }

    /**
     * Required unless template_id is set.
     *
     * The body of the email you wish to send. Typically, customers include their own HTML templates here.
     * Must include [unsubscribe_url] in an <a> tag somewhere in the email.
     *
     * Note: any malformed HTML content will be sent to users. Please double-check your HTML is valid.
     *
     * @param non-empty-string $value HTML supported
     * @return $this
     */
    public function setEmailBody(string $value): self
    {
        return $this->setAttribute(CNO::EMAIL_BODY, $value);
    }

    /**
     * The name the email is from. If not specified, will default to "from name" set in the
     *  OneSignal Dashboard Email Settings.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setEmailFromName(string $value): self
    {
        return $this->setAttribute(CNO::EMAIL_FROM_NAME, $value);
    }

    /**
     * The email address the email is from. If not specified, will default to "from email" set in the
     *  OneSignal Dashboard Email Settings.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setEmailFromAddress(string $value): self
    {
        return $this->setAttribute(CNO::EMAIL_FROM_ADDRESS, $value);
    }

    /**
     * The preheader text for the email.
     * Preheader is the preview text displayed immediately after an email subject that provides additional context about
     *  the email content. If not specified, will default to null.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setEmailPreheader(string $value): self
    {
        return $this->setAttribute(CNO::EMAIL_PREHEADER, $value);
    }

    /**
     * If set to true the URLs included in the email will not change to link tracking URLs and will stay the same as
     *  originally set. Best used for emails containing Universal Links.
     *
     * Default is false.
     *
     * @param bool $value
     * @return $this
     */
    public function setDisableEmailClickTracking(bool $value): self
    {
        return $this->setAttribute(CNO::DISABLE_EMAIL_CLICK_TRACKING, $value);
    }

    /**
     * Use a template you set up on our dashboard.
     *
     * The template_id is the UUID found in the URL when viewing a template on our dashboard.
     *
     * Example: be4a8044-bbd6-11e4-a581-000c2940e62c
     *
     * @param non-empty-string $value UUID
     * @return $this
     */
    public function setTemplateId(string $value): self
    {
        return $this->setAttribute(CNO::TEMPLATE_ID, $value);
    }
}
