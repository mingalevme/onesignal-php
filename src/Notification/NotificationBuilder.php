<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal\Notification;

use DateTimeInterface;
use InvalidArgumentException;
use Mingalevme\OneSignal\CreateNotificationOptions as CNO;

/**
 * NOTE:
 * The package (mingalevme/onesignal) DOES NOT check any limits like filters (200), include_player_ids (2000), etc.
 *  in runtime cause the limits can change in the future.
 *
 * @see https://documentation.onesignal.com/reference/create-notification
 */
final class NotificationBuilder
{
    use NotificationBuilderPushChannelPropertiesTrait;

    /** @var array<non-empty-string, mixed> */
    private array $data = [
        CNO::FILTERS => [],
    ];

    /**
     * @return $this
     */
    public static function new(): self
    {
        return new self();
    }

    private function __construct()
    {
    }

    public function build(): Notification
    {
        if (empty($this->data)) {
            throw new InvalidArgumentException('Notification cannot be empty');
        }

        if (empty($this->data[CNO::CONTENTS])) {
            if (empty($this->data[CNO::CONTENT_AVAILABLE])) {
                if (empty($this->data[CNO::TEMPLATE_ID])) {
                    throw new InvalidArgumentException(
                        'Title is required unless content_available=true or template_id is set'
                    );
                }
            }
        }

        if (!empty($this->data[CNO::DELIVERY_TIME_OF_DAY])) {
            if (($this->data[CNO::DELAYED_OPTION] ?? null) !== CNO::DELAYED_OPTION_TIMEZONE) {
                throw new InvalidArgumentException(
                    'delivery_time_of_day mist be used with delayed_option=timezone'
                );
            }
        }

        return new Notification($this->data);
    }

    //
    // Targeting
    // Note: Select ONE OF the three targeting options: Segments, Filters, and Specific Devices.
    //

    // Targeting / Segments

    /**
     * @param non-empty-list<non-empty-string> $value
     * @return $this
     */
    public function setIncludedSegments(array $value): self
    {
        $this->data[CNO::INCLUDED_SEGMENTS] = $value;
        return $this;
    }


    /**
     * @param non-empty-list<non-empty-string> $value
     * @return $this
     */
    public function setExcludedSegments(array $value): self
    {
        $this->data[CNO::EXCLUDED_SEGMENTS] = $value;
        return $this;
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
        return $this->addFilter(CNO::FILTERS_FIRST_SESSION, [
            CNO::FILTERS_RELATION => $relation,
            CNO::FILTERS_VALUE => $value,
        ]);
    }

    /**
     * @param non-empty-string $relation
     * @psalm-param '>'|'<'|'=' $relation
     * @phpstan-param '>'|'<'|'=' $relation
     * @param int<1, max> $value
     * @return $this
     */
    public function addFilterAmountSpent(string $relation, int $value): self
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
     * @param non-empty-string $key
     * @param numeric-string $value
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
     * @param non-empty-string $relation
     * @psalm-param '>'|'<'|'='|'!='|'time_elapsed_gt'|'time_elapsed_lt' $relation
     * @phpstan-param '>'|'<'|'='|'!='|'time_elapsed_gt'|'time_elapsed_lt' $relation
     * @param non-empty-string $key
     * @param non-empty-string $value
     * @return $this
     */
    public function addFilterTag(string $relation, string $key, string $value): self
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
     * @param non-empty-string $relation
     * @psalm-param '='|'!=' $relation
     * @phpstan-param '='|'!=' $relation
     * @param non-empty-string $value 2 character language code:
     *  https://documentation.onesignal.com/docs/language-localization
     * @return $this
     */
    public function addFilterLanguage(string $relation, string $value): self
    {
        return $this->addFilter(CNO::FILTERS_LANGUAGE, [
            CNO::FILTERS_RELATION => $relation,
            CNO::FILTERS_VALUE => $value,
        ]);
    }

    /**
     * @param non-empty-string $relation
     * @psalm-param '=' $relation
     * @phpstan-param '=' $relation
     * @param non-empty-string $value 2-digit Country code
     * @return $this
     */
    public function addFilterCountry(string $relation, string $value): self
    {
        return $this->addFilter(CNO::FILTERS_COUNTRY, [
            CNO::FILTERS_RELATION => $relation,
            CNO::FILTERS_VALUE => $value,
        ]);
    }

    /**
     * NOTE: Only for sending Push Notifications
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

    // Targeting / Specific Devices.

    /**
     * Specific playerIds to send your notification to. Does not require API Auth Key.
     * Example: ["1dd608f2-c6a1-11e3-851d-000c2940e62c"]
     * Limit of 2,000 entries per REST API call
     *
     * @param non-empty-list<string> $value
     * @return $this
     */
    public function setIncludePlayerIds(array $value): self
    {
        $this->data[CNO::INCLUDE_PLAYER_IDS] = $value;
        return $this;
    }

    /**
     * Target specific devices by custom user IDs assigned via API.
     * Example: [“custom-id-assigned-by-api”]
     * REQUIRED: REST API Key Authentication
     * Limit of 2,000 entries per REST API call.
     * Note: If targeting push, email, or sms subscribers with same ids, use with channel_for_external_user_ids
     *  to indicate you are sending a push or email or sms.
     *
     * @param non-empty-list<string> $value
     * @return $this
     */
    public function setIncludeExternalUserIds(array $value): self
    {
        $this->data[CNO::INCLUDE_EXTERNAL_USER_IDS] = $value;
        return $this;
    }

    /**
     * Recommended for Sending Emails - Target specific email addresses.
     * If an email does not correspond to an existing user, a new user will be created.
     * Example: ["nick@catfac.ts"]
     * Limit of 2,000 entries per REST API call
     *
     * @param non-empty-list<string> $value
     * @return $this
     */
    public function setIncludeEmailTokens(array $value): self
    {
        $this->data[CNO::INCLUDE_EMAIL_TOKENS] = $value;
        return $this;
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
     * @param non-empty-list<string> $value
     * @return $this
     */
    public function setIncludePhoneNumbers(array $value): self
    {
        $this->data[CNO::INCLUDE_PHONE_NUMBERS] = $value;
        return $this;
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
     * @param non-empty-list<string> $value
     * @return $this
     */
    public function setIncludeIosTokens(array $value): self
    {
        $this->data[CNO::INCLUDE_IOS_TOKENS] = $value;
        return $this;
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
     * @param non-empty-list<string> $value
     * @return $this
     */
    public function setIncludeWpWnsUris(array $value): self
    {
        $this->data[CNO::INCLUDE_WP_WNS_URIS] = $value;
        return $this;
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
     * @param non-empty-list<string> $value
     * @return $this
     */
    public function setIncludeAmazonRegIds(array $value): self
    {
        $this->data[CNO::INCLUDE_AMAZON_REG_IDS] = $value;
        return $this;
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
     * @param non-empty-list<string> $value
     * @return $this
     */
    public function setIncludeChromeRegIds(array $value): self
    {
        $this->data[CNO::INCLUDE_CHROME_REG_IDS] = $value;
        return $this;
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
     * @param non-empty-list<string> $value
     * @return $this
     */
    public function setIncludeChromeWebRegIds(array $value): self
    {
        $this->data[CNO::INCLUDE_CHROME_WEB_REG_IDS] = $value;
        return $this;
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
     * @param non-empty-list<string> $value
     * @return $this
     */
    public function setIncludeAndroidRegIds(array $value): self
    {
        $this->data[CNO::INCLUDE_ANDROID_REG_IDS] = $value;
        return $this;
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
        $this->data[CNO::IS_IOS] = $value;
        return $this;
    }

    /**
     * Indicates whether to send to all devices registered under your app's Google Android platform.
     *
     * @param bool $value
     * @return $this
     */
    public function setIsAndroid(bool $value): self
    {
        $this->data[CNO::IS_ANDROID] = $value;
        return $this;
    }

    /**
     * Indicates whether to send to all devices registered under your app's Huawei Android platform.
     *
     * @param bool $value
     * @return $this
     */
    public function setIsHuawei(bool $value): self
    {
        $this->data[CNO::IS_HUAWEI] = $value;
        return $this;
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
        $this->data[CNO::IS_ANY_WEB] = $value;
        return $this;
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
        $this->data[CNO::IS_CHROME_WEB] = $value;
        return $this;
    }

    /**
     * Indicates whether to send to all Mozilla Firefox desktop users registered under your Firefox web push platform.
     *
     * @param bool $value
     * @return $this
     */
    public function setIsFirefox(bool $value): self
    {
        $this->data[CNO::IS_FIREFOX] = $value;
        return $this;
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
        $this->data[CNO::IS_SAFARI] = $value;
        return $this;
    }

    /**
     * Indicates whether to send to all devices registered under your app's Windows platform.
     *
     * @param bool $value
     * @return $this
     */
    public function setIsWpWns(bool $value): self
    {
        $this->data[CNO::IS_WP_WNS] = $value;
        return $this;
    }

    /**
     * Indicates whether to send to all devices registered under your app's Amazon Fire platform.
     *
     * @param bool $value
     * @return $this
     */
    public function setIsAdm(bool $value): self
    {
        $this->data[CNO::IS_ADM] = $value;
        return $this;
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
        $this->data[CNO::IS_CHROME] = $value;
        return $this;
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
        $this->data[CNO::CHANNEL_FOR_EXTERNAL_USER_IDS] = $value;
        return $this;
    }


    // BODY PARAMS

    /**
     * @param non-empty-string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->data[CNO::NAME] = $name;
        return $this;
    }

    /**
     * @param non-empty-string $externalId
     * @return $this
     */
    public function setExternalId(string $externalId): self
    {
        $this->data[CNO::EXTERNAL_ID] = $externalId;
        return $this;
    }

    public function setSendAfter(DateTimeInterface $dateTime): self
    {
        $this->data[CNO::SEND_AFTER] = $dateTime->format('Y-m-d H:i:s \G\M\TO');
        return $this;
    }

    /**
     * @param non-empty-string $value
     * @psalm-param 'timezone'|'last-active' $value
     * @phpstan-param 'timezone'|'last-active' $value
     * @return $this
     */
    public function setDelayedOption(string $value): self
    {
        $this->data[CNO::DELAYED_OPTION] = $value;
        return $this;
    }

    /**
     * @param non-empty-string $value Ex.: "9:00AM", "21:45", "9:45:30"
     * @return $this
     */
    public function setDeliveryTimeOfDay(string $value): self
    {
        $this->data[CNO::DELIVERY_TIME_OF_DAY] = $value;
        return $this;
    }

    /**
     * @param int<0, max> $value
     * @return $this
     */
    public function setThrottleRatePerMinute(int $value): self
    {
        $this->data[CNO::THROTTLE_RATE_PER_MINUTE] = $value;
        return $this;
    }
}
