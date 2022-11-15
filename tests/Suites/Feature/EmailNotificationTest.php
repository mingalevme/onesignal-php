<?php

declare(strict_types=1);

namespace Mingalevme\Tests\OneSignal\Suites\Feature;

use Mingalevme\OneSignal\Notification\EmailNotification;

class EmailNotificationTest extends AbstractFeatureTestCase
{
    public function testEmailBodyNotification(): void
    {
        $notification = EmailNotification::createBodyNotification('subject', 'body');
        $attributes = [
            'email_subject' => 'subject',
            'email_body' => 'body',
            'included_segments' => ['All'],
        ];
        self::assertNotificationHasAttributes($attributes, $notification, true);
    }

    public function testEmailTemplateNotification(): void
    {
        $notification = EmailNotification::createTemplateNotification('subject', 'template-id');
        $attributes = [
            'email_subject' => 'subject',
            'template_id' => 'template-id',
            'included_segments' => ['All'],
        ];
        self::assertNotificationHasAttributes($attributes, $notification, true);
    }

    public function testEmailContent(): void
    {
        $notification = EmailNotification::createBodyNotification('subject', 'body')
            ->setEmailFromName('from')
            ->setEmailFromAddress('from@example.com')
            ->setEmailPreheader('preheader')
            ->setDisableEmailClickTracking(true);
        $attributes = [
            'email_subject' => 'subject',
            'email_body' => 'body',
            'email_from_name' => 'from',
            'email_from_address' => 'from@example.com',
            'email_preheader' => 'preheader',
            'disable_email_click_tracking' => true,
            'included_segments' => ['All'],
        ];
        self::assertNotificationHasAttributes($attributes, $notification, true);
    }
}
