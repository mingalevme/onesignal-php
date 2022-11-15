<?php

declare(strict_types=1);

namespace Mingalevme\Tests\OneSignal\Suites\Feature;

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
}
