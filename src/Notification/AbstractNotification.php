<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal\Notification;

use DateTimeInterface;
use InvalidArgumentException;
use Mingalevme\OneSignal\CreateNotificationOptions as CNO;
use Mingalevme\Tests\OneSignal\Suites\Feature\NotificationBuildingTest;

/**
 * NOTE:
 * The package (mingalevme/onesignal) DOES NOT check any limits like filters (200), include_player_ids (2000), etc.
 *  in runtime cause the limits can change in the future.
 *
 * @see https://documentation.onesignal.com/reference/create-notification
 *
 * @see NotificationBuildingTest
 */
abstract class AbstractNotification implements NotificationInterface
{
    /** @var array<non-empty-string, mixed> */
    protected array $data = [
        CNO::FILTERS => [],
    ];

    protected bool $isTargetSet = false;

    /**
     * @return non-empty-array<non-empty-string, mixed>
     */
    public function toOneSignalData(): array
    {
        $data = array_filter($this->data);

        if (empty($data)) {
            throw new InvalidArgumentException('Notification cannot be empty');
        }

        return $data;
    }

    /**
     * @param non-empty-string $name
     * @param mixed $value
     * @return $this
     */
    public function setAttribute(string $name, $value): self
    {
        $this->data[$name] = $value;
        return $this;
    }

    /**
     * @param array<non-empty-string, mixed> $attributes
     * @return $this
     */
    public function setAttributes(array $attributes): self
    {
        /** @psalm-suppress MixedAssignment */
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
        return $this;
    }

    /**
     * @param non-empty-string $name
     * @param mixed $value
     * @return $this
     */
    protected function setTargetAttribute(string $name, $value): self
    {
        $this->isTargetSet = true;
        return $this->setAttribute($name, $value);
    }

    /**
     * @param non-empty-string $attributeName
     * @param non-empty-string|non-empty-array<non-empty-string, non-empty-string> $text
     * @return $this
     */
    protected function setLocalizedText(string $attributeName, $text): self
    {
        if (is_string($text)) {
            return $this->setAttribute($attributeName, [
                'en' => $text,
            ]);
        }

        if (empty($text['en'])) {
            throw new InvalidArgumentException('Invalid or missing default text of notification (content["en"])');
        }

        return $this->setAttribute($attributeName, $text);
    }

    //
    // Targeting
    // Note: Select ONE OF the three targeting options: Segments, Filters, and Specific Devices.
    //

    // Targeting / Segments

    /**
     * @param non-empty-string|non-empty-list<non-empty-string> $value
     * @return $this
     */
    public function setIncludedSegments($value): self
    {
        return $this->setTargetAttribute(CNO::INCLUDED_SEGMENTS, (array)$value);
    }


    /**
     * @param non-empty-string|non-empty-list<non-empty-string> $value
     * @return $this
     */
    public function setExcludedSegments($value): self
    {
        return $this->setAttribute(CNO::EXCLUDED_SEGMENTS, (array)$value);
    }

    // Targeting / Filters

    /**
     * NOTE:
     *  For performance reasons, a maximum of 200 entries can be used at a time.
     *  The 200 entries limit includes the "field" entry and "OR" entries - each would count towards the 200 limit.
     *
     * The package (mingalevme/onesignal) DOES NOT check the of the filters in runtime cause the limit can change
     *  in the future
     *
     * @param non-empty-string $field
     * @param array<non-empty-string, non-empty-string|int> $value
     * @return $this
     */
    private function addFilter(string $field, array $value): self
    {
        /**
         * @psalm-suppress MixedArrayAssignment
         * @phpstan-ignore-next-line
         */
        $this->data[CNO::FILTERS][] = [
                CNO::FILTERS_FIELD => $field,
            ] + $value;

        $this->isTargetSet = true;

        return $this;
    }

    public function addFilterOrClause(): self
    {
        /**
         * @psalm-suppress MixedArrayAssignment
         * @phpstan-ignore-next-line
         */
        $this->data[CNO::FILTERS][] = [
            CNO::FILTERS_OPERATOR => 'OR',
        ];
        return $this;
    }

    /**
     * @param non-empty-string $relation
     * @psalm-param  '>'|'<' $relation
     * @phpstan-param  '>'|'<' $relation
     * @param numeric $hoursAgo
     * @return $this
     */
    public function addFilterLastSession(string $relation, $hoursAgo): self
    {
        return $this->addFilter(CNO::FILTERS_LAST_SESSION, [
            CNO::FILTERS_RELATION => $relation,
            CNO::FILTERS_HOURS_AGO => (string)$hoursAgo,
        ]);
    }

    /**
     * @param non-empty-string $relation
     * @psalm-param '>'|'<' $relation
     * @phpstan-param '>'|'<' $relation
     * @param numeric $hoursAgo
     * @return $this
     */
    public function addFilterFirstSession(string $relation, $hoursAgo): self
    {
        return $this->addFilter(CNO::FILTERS_FIRST_SESSION, [
            CNO::FILTERS_RELATION => $relation,
            CNO::FILTERS_HOURS_AGO => (string)$hoursAgo,
        ]);
    }

    /**
     * @param non-empty-string $relation
     * @psalm-param '>'|'<'|'='|'!=' $relation
     * @phpstan-param '>'|'<'|'='|'!=' $relation
     * @param int<1, max> $value
     * @return $this
     */
    public function addFilterSessionCount(string $relation, int $value): self
    {
        return $this->addFilter(CNO::FILTERS_SESSION_COUNT, [
            CNO::FILTERS_RELATION => $relation,
            CNO::FILTERS_VALUE => $value,
        ]);
    }

    /**
     * @param non-empty-string $relation
     * @psalm-param '>'|'<' $relation
     * @phpstan-param '>'|'<' $relation
     * @param int<1, max> $value
     * @return $this
     */
    public function addFilterSessionTime(string $relation, int $value): self
    {
        return $this->addFilter(CNO::FILTERS_SESSION_TIME, [
            CNO::FILTERS_RELATION => $relation,
            CNO::FILTERS_VALUE => $value,
        ]);
    }

    /**
     * @param non-empty-string $relation
     * @psalm-param '>'|'<'|'=' $relation
     * @phpstan-param '>'|'<'|'=' $relation
     * @param numeric-string $value float<0, max> Amount in USD a user has spent on IAP (In-App Purchases).
     *  Example: "0.99"
     * @return $this
     */
    public function addFilterAmountSpent(string $relation, string $value): self
    {
        return $this->addFilter(CNO::FILTERS_AMOUNT_SPENT, [
            CNO::FILTERS_RELATION => $relation,
            CNO::FILTERS_VALUE => $value,
        ]);
    }

    /**
     * @param non-empty-string $relation
     * @psalm-param '>'|'<'|'=' $relation
     * @phpstan-param '>'|'<'|'=' $relation
     * @param non-empty-string $key SKU purchased in your app as an IAP (In-App Purchases).
     *  Example: "com.domain.100coinpack"
     * @param numeric-string $value Value of SKU to compare to. Example: "0.99"
     * @return $this
     */
    public function addFilterBoughtSku(string $relation, string $key, string $value): self
    {
        return $this->addFilter(CNO::FILTERS_BOUGHT_SKU, [
            CNO::FILTERS_RELATION => $relation,
            CNO::FILTERS_KEY => $key,
            CNO::FILTERS_VALUE => $value,
        ]);
    }

    /**
     * @param non-empty-string $key
     * @param non-empty-string $relation
     * @psalm-param '>'|'<'|'='|'!='|'time_elapsed_gt'|'time_elapsed_lt' $relation
     * @phpstan-param '>'|'<'|'='|'!='|'time_elapsed_gt'|'time_elapsed_lt' $relation
     * @param non-empty-string $value
     * @return $this
     */
    public function addFilterTag(string $key, string $relation, string $value): self
    {
        return $this->addFilter(CNO::FILTERS_TAG, [
            CNO::FILTERS_RELATION => $relation,
            CNO::FILTERS_KEY => $key,
            CNO::FILTERS_VALUE => $value,
        ]);
    }

    /**
     * @param non-empty-string $key
     * @return $this
     */
    public function addFilterTagExists(string $key): self
    {
        return $this->addFilter(CNO::FILTERS_TAG, [
            CNO::FILTERS_RELATION => CNO::FILTERS_RELATION_EXISTS,
            CNO::FILTERS_VALUE => $key,
        ]);
    }

    /**
     * @param non-empty-string $key
     * @return $this
     */
    public function addFilterTagNotExists(string $key): self
    {
        return $this->addFilter(CNO::FILTERS_TAG, [
            CNO::FILTERS_RELATION => CNO::FILTERS_RELATION_NOT_EXISTS,
            CNO::FILTERS_VALUE => $key,
        ]);
    }

    /**
     * @param non-empty-string $relation
     * @psalm-param '='|'!=' $relation
     * @phpstan-param '='|'!=' $relation
     * @param non-empty-string $value 2 character language code:
     *  https://documentation.onesignal.com/docs/language-localization
     * @return $this
     */
    protected function addFilterLanguage(string $relation, string $value): self
    {
        return $this->addFilter(CNO::FILTERS_LANGUAGE, [
            CNO::FILTERS_RELATION => $relation,
            CNO::FILTERS_VALUE => $value,
        ]);
    }

    /**
     * @param non-empty-string $value 2 character language code:
     *  https://documentation.onesignal.com/docs/language-localization
     * @return $this
     */
    public function addFilterLanguageEquals(string $value): self
    {
        return $this->addFilterLanguage('=', $value);
    }

    /**
     * @param non-empty-string $value 2 character language code:
     *  https://documentation.onesignal.com/docs/language-localization
     * @return $this
     */
    public function addFilterLanguageNotEquals(string $value): self
    {
        return $this->addFilterLanguage('!=', $value);
    }

    /**
     * @param non-empty-string $relation
     * @psalm-param '>'|'<'|'='|'!=' $relation
     * @phpstan-param '>'|'<'|'='|'!=' $relation
     * @param non-empty-string $value
     * @return $this
     */
    public function addFilterAppVersion(string $relation, string $value): self
    {
        return $this->addFilter(CNO::FILTERS_APP_VERSION, [
            CNO::FILTERS_RELATION => $relation,
            CNO::FILTERS_VALUE => $value,
        ]);
    }

    /**
     * @param int<0, max> $radius
     * @param numeric-string $lat
     * @param numeric-string $long
     * @return $this
     */
    public function addFilterLocation(int $radius, string $lat, string $long): self
    {
        return $this->addFilter(CNO::FILTERS_LOCATION, [
            CNO::FILTERS_RADIUS => $radius,
            CNO::FILTERS_LAT => $lat,
            CNO::FILTERS_LONG => $long,
        ]);
    }

    /**
     * Use this for targeting push subscribers associated with an email set with all SDK "setEmail" methods
     *
     * NOTE: Only for sending Push Notifications .To send emails to specific email addresses use
     *  include_email_tokens-parameter
     *
     * @param non-empty-string $email
     * @return $this
     */
    public function addFilterEmail(string $email): self
    {
        return $this->addFilter(CNO::FILTERS_EMAIL, [
            CNO::FILTERS_VALUE => $email,
        ]);
    }

    /**
     * @param non-empty-string $value 2-digit Country code
     * @return $this
     */
    public function addFilterCountryEquals(string $value): self
    {
        return $this->addFilter(CNO::FILTERS_COUNTRY, [
            CNO::FILTERS_RELATION => '=',
            CNO::FILTERS_VALUE => $value,
        ]);
    }

    // Targeting / Specific Devices

    /**
     * Specific playerIds to send your notification to. Does not require API Auth Key.
     * Example: ["1dd608f2-c6a1-11e3-851d-000c2940e62c"]
     * Limit of 2,000 entries per REST API call
     *
     * @param non-empty-string|non-empty-list<non-empty-string> $value
     * @return $this
     */
    public function setIncludePlayerIds($value): self
    {
        return $this->setTargetAttribute(CNO::INCLUDE_PLAYER_IDS, (array)$value);
    }

    /**
     * Target specific devices by custom user IDs assigned via API.
     * Example: [“custom-id-assigned-by-api”]
     * REQUIRED: REST API Key Authentication
     * Limit of 2,000 entries per REST API call.
     * Note: If targeting push, email, or sms subscribers with same ids, use with channel_for_external_user_ids
     *  to indicate you are sending a push or email or sms.
     *
     * @param non-empty-string|non-empty-list<non-empty-string> $value
     * @return $this
     */
    public function setIncludeExternalUserIds($value): self
    {
        return $this->setTargetAttribute(CNO::INCLUDE_EXTERNAL_USER_IDS, (array)$value);
    }

    /**
     * Recommended for Sending Emails - Target specific email addresses.
     * If an email does not correspond to an existing user, a new user will be created.
     * Example: ["nick@catfac.ts"]
     * Limit of 2,000 entries per REST API call
     *
     * @param non-empty-string|non-empty-list<non-empty-string> $value
     * @return $this
     */
    public function setIncludeEmailTokens($value): self
    {
        return $this->setTargetAttribute(CNO::INCLUDE_EMAIL_TOKENS, (array)$value);
    }

    /**
     * Recommended for Sending SMS - Target specific phone numbers.
     * The phone number should be in the E.164 format.
     * Phone number should be an existing subscriber on OneSignal.
     * Refer our docs to learn how to add phone numbers to OneSignal.
     *
     * Example phone number: +1999999999
     *
     * Limit of 2,000 entries per REST API call
     *
     * @param non-empty-string|non-empty-list<non-empty-string> $value
     * @return $this
     */
    public function setIncludePhoneNumbers($value): self
    {
        return $this->setTargetAttribute(CNO::INCLUDE_PHONE_NUMBERS, (array)$value);
    }

    /**
     * Not Recommended: Please consider using include_player_ids or include_external_user_ids instead.
     * Target using iOS device tokens.
     * Warning: Only works with Production tokens.
     *
     * All non-alphanumeric characters must be removed from each token.
     * If a token does not correspond to an existing user, a new user will be created.
     *
     * Example: ["ce777617da7f548fe7a9ab6febb56cf39fba6d38203..."]
     *
     * Limit of 2,000 entries per REST API call
     *
     * @param non-empty-string|non-empty-list<non-empty-string> $value
     * @return $this
     */
    public function setIncludeIosTokens($value): self
    {
        return $this->setTargetAttribute(CNO::INCLUDE_IOS_TOKENS, (array)$value);
    }

    /**
     * Not Recommended: Please consider using include_player_ids or include_external_user_ids instead.
     *
     * Target using Windows URIs. If a token does not correspond to an existing user, a new user will be created.
     *
     * Example: ["http://s.notify.live.net/u/1/bn1/HmQAAACPaLDr-..."]
     *
     * Limit of 2,000 entries per REST API call
     *
     * @param non-empty-string|non-empty-list<non-empty-string> $value
     * @return $this
     */
    public function setIncludeWpWnsUris($value): self
    {
        return $this->setTargetAttribute(CNO::INCLUDE_WP_WNS_URIS, (array)$value);
    }

    /**
     * Not Recommended: Please consider using include_player_ids or include_external_user_ids instead.
     *
     * Target using Amazon ADM registration IDs.
     * If a token does not correspond to an existing user, a new user will be created.
     *
     * Example: ["amzn1.adm-registration.v1.XpvSSUk0Rc3hTVVV..."]
     *
     * Limit of 2,000 entries per REST API call
     *
     * @param non-empty-string|non-empty-list<non-empty-string> $value
     * @return $this
     */
    public function setIncludeAmazonRegIds($value): self
    {
        return $this->setTargetAttribute(CNO::INCLUDE_AMAZON_REG_IDS, (array)$value);
    }

    /**
     * Not Recommended: Please consider using include_player_ids or include_external_user_ids instead.
     *
     * Target using Chrome App registration IDs.
     * If a token does not correspond to an existing user, a new user will be created.
     *
     * Example: ["APA91bEeiUeSukAAUdnw3O2RB45FWlSpgJ7Ji_..."]
     *
     * Limit of 2,000 entries per REST API call
     *
     * @param non-empty-string|non-empty-list<non-empty-string> $value
     * @return $this
     */
    public function setIncludeChromeRegIds($value): self
    {
        return $this->setTargetAttribute(CNO::INCLUDE_CHROME_REG_IDS, (array)$value);
    }

    /**
     * Not Recommended: Please consider using include_player_ids or include_external_user_ids instead.
     *
     * Target using Chrome Web Push registration IDs.
     * If a token does not correspond to an existing user, a new user will be created.
     *
     * Example: ["APA91bEeiUeSukAAUdnw3O2RB45FWlSpgJ7Ji_..."]
     *
     * Limit of 2,000 entries per REST API call
     *
     * @param non-empty-string|non-empty-list<non-empty-string> $value
     * @return $this
     */
    public function setIncludeChromeWebRegIds($value): self
    {
        return $this->setTargetAttribute(CNO::INCLUDE_CHROME_WEB_REG_IDS, (array)$value);
    }

    /**
     * Not Recommended: Please consider using include_player_ids or include_external_user_ids instead.
     *
     * Target using Android device registration IDs.
     * If a token does not correspond to an existing user, a new user will be created.
     *
     * Example: ["APA91bEeiUeSukAAUdnw3O2RB45FWlSpgJ7Ji_..."]
     *
     * Limit of 2,000 entries per REST API call
     *
     * @param non-empty-string|non-empty-list<non-empty-string> $value
     * @return $this
     */
    public function setIncludeAndroidRegIds($value): self
    {
        return $this->setTargetAttribute(CNO::INCLUDE_ANDROID_REG_IDS, (array)$value);
    }

    /*
     * Platform to Deliver To
     *
     * By default, OneSignal will send to every platform (each platform equals true).
     *
     * To only send to specific platforms, you may pass in true on one or more of these boolean parameters
     *  corresponding to the platform you wish to target.
     * If you do so, all other platforms will be set to false and will not be delivered to.
     */

    /**
     * Indicates whether to send to all devices registered under your app's Apple iOS platform.
     *
     * @param bool $value
     * @return $this
     */
    public function setIsIos(bool $value): self
    {
        return $this->setAttribute(CNO::IS_IOS, $value);
    }

    /**
     * Indicates whether to send to all devices registered under your app's Google Android platform.
     *
     * @param bool $value
     * @return $this
     */
    public function setIsAndroid(bool $value): self
    {
        return $this->setAttribute(CNO::IS_ANDROID, $value);
    }

    /**
     * Indicates whether to send to all devices registered under your app's Huawei Android platform.
     *
     * @param bool $value
     * @return $this
     */
    public function setIsHuawei(bool $value): self
    {
        return $this->setAttribute(CNO::IS_HUAWEI, $value);
    }

    /**
     * Indicates whether to send to all subscribed web browser users, including Chrome, Firefox, and Safari.
     *
     * You may use this instead as a combined flag instead of separately enabling isChromeWeb, isFirefox, and isSafari,
     *  though the three options are equivalent to this one.
     *
     * @param bool $value
     * @return $this
     */
    public function setIsAnyWeb(bool $value): self
    {
        return $this->setAttribute(CNO::IS_ANY_WEB, $value);
    }

    /**
     * Indicates whether to send to all Google Chrome, Chrome on Android, and Mozilla Firefox users registered under
     *  your Chrome & Firefox web push platform.
     *
     * @param bool $value
     * @return $this
     */
    public function setIsChromeWeb(bool $value): self
    {
        return $this->setAttribute(CNO::IS_CHROME_WEB, $value);
    }

    /**
     * Indicates whether to send to all Mozilla Firefox desktop users registered under your Firefox web push platform.
     *
     * @param bool $value
     * @return $this
     */
    public function setIsFirefox(bool $value): self
    {
        return $this->setAttribute(CNO::IS_FIREFOX, $value);
    }

    /**
     * Does not support iOS Safari.
     * Indicates whether to send to all Apple's Safari desktop users registered under your Safari web push platform.
     * Read more: iOS Safari (https://onesignal.com/blog/the-state-of-ios-web-push-in-2020/)
     *
     * @param bool $value
     * @return $this
     */
    public function setIsSafari(bool $value): self
    {
        return $this->setAttribute(CNO::IS_SAFARI, $value);
    }

    /**
     * Indicates whether to send to all devices registered under your app's Windows platform.
     *
     * @param bool $value
     * @return $this
     */
    public function setIsWpWns(bool $value): self
    {
        return $this->setAttribute(CNO::IS_WP_WNS, $value);
    }

    /**
     * Indicates whether to send to all devices registered under your app's Amazon Fire platform.
     *
     * @param bool $value
     * @return $this
     */
    public function setIsAdm(bool $value): self
    {
        return $this->setAttribute(CNO::IS_ADM, $value);
    }

    /**
     * This flag is not used for web push Please see isChromeWeb for sending to web push users.
     * This flag only applies to Google Chrome Apps & Extensions.
     *
     * Indicates whether to send to all devices registered under your app's Google Chrome Apps & Extension platform.
     *
     * @param bool $value
     * @return $this
     */
    public function setIsChrome(bool $value): self
    {
        return $this->setAttribute(CNO::IS_CHROME, $value);
    }

    /**
     * Indicates if the message type when targeting with include_external_user_ids for cases where an email, sms,
     *  and/or push subscribers have the same external user id.
     *
     * Example:
     * Use the string "push" to indicate you are sending a push notification or the string "email"for sending emails
     *  or "sms"for sending SMS.
     *
     * @param non-empty-string $value
     * @psalm-param 'push'|'email'|'sms' $value
     * @phpstan-param 'push'|'email'|'sms' $value
     * @return $this
     */
    public function setChannelForExternalUserIds(string $value): self
    {
        return $this->setAttribute(CNO::CHANNEL_FOR_EXTERNAL_USER_IDS, $value);
    }

    // BODY PARAMS

    /**
     * An internal name to assist with your campaign organization for tracking message within the OneSignal dashboard
     *  or export analytics.
     *
     * This does not get displayed on the message itself. Not shown to end user.
     *
     * Required for SMS Messages.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setName(string $value): self
    {
        return $this->setAttribute(CNO::NAME, $value);
    }

    /**
     * Correlation and idempotency key.
     * A request received with this parameter will first look for another notification with the same external_id.
     * If one exists, a notification will not be sent, and result of the previous operation will instead be returned.
     * Therefore, if you plan on using this feature, it's important to use a good source of randomness to generate
     *  the UUID passed here.
     * This key is only idempotent for 30 days. After 30 days, the notification could be removed from our system and
     *  a notification with the same external_id will be sent again.
     *
     * See Idempotent Notification Requests for more details:
     *  https://documentation.onesignal.com/docs/idempotent-notification-requests
     *
     * This is not the "external_user_id".
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setExternalId(string $value): self
    {
        return $this->setAttribute(CNO::EXTERNAL_ID, $value);
    }

    /**
     * Schedule notification for future delivery. API defaults to UTC.
     *
     * @param DateTimeInterface $dateTime
     * @return $this
     */
    public function setSendAfter(DateTimeInterface $dateTime): self
    {
        return $this->setAttribute(CNO::SEND_AFTER, $dateTime->format('Y-m-d H:i:s \G\M\TO'));
    }

    /**
     * @param non-empty-string $value
     * @psalm-param 'timezone'|'last-active' $value
     * @phpstan-param 'timezone'|'last-active' $value
     * @return $this
     */
    public function setDelayedOption(string $value): self
    {
        return $this->setAttribute(CNO::DELAYED_OPTION, $value);
    }

    /**
     * @param non-empty-string $value Ex.: "9:00AM", "21:45", "9:45:30"
     * @return $this
     */
    public function setDeliveryTimeOfDay(string $value): self
    {
        return $this->setAttribute(CNO::DELIVERY_TIME_OF_DAY, $value);
    }

    /**
     * @param int<0, max> $value
     * @return $this
     */
    public function setThrottleRatePerMinute(int $value): self
    {
        return $this->setAttribute(CNO::THROTTLE_RATE_PER_MINUTE, $value);
    }
}
