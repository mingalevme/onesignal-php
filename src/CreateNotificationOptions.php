<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal;

class CreateNotificationOptions
{
    public const NAME = 'name';

    // Send to Segments
    public const SEGMENTS_ALL = 'All';
    public const SEGMENTS_SUBSCRIBED_USERS = 'Subscribed Users';
    public const SEGMENTS_ACTIVE_USERS = 'Active Users';
    public const SEGMENTS_INACTIVE_USERS = 'Inactive Users';
    public const SEGMENTS_ENGAGED_USERS = 'Engaged Users';
    public const INCLUDED_SEGMENTS = 'included_segments';
    public const EXCLUDED_SEGMENTS = 'excluded_segments';

    // Send to Users Based on Filters
    public const FILTERS = 'filters';
    public const FILTERS_FIELD = 'field';
    public const FILTERS_OPERATOR = 'operator';
    public const FILTERS_RELATION = 'relation';
    public const FILTERS_RELATION_EXISTS = 'exists';
    public const FILTERS_RELATION_NOT_EXISTS = 'not_exists';
    public const FILTERS_KEY = 'key';
    public const FILTERS_VALUE = 'value';
    public const FILTERS_LAST_SESSION = 'last_session';
    public const FILTERS_FIRST_SESSION = 'first_session';
    public const FILTERS_SESSION_COUNT = 'session_count';
    public const FILTERS_SESSION_TIME = 'session_time';
    public const FILTERS_AMOUNT_SPENT = 'amount_spent';
    public const FILTERS_BOUGHT_SKU = 'bought_sku';
    public const FILTERS_TAG = 'tag';
    public const FILTERS_LANGUAGE = 'language';
    public const FILTERS_APP_VERSION = 'app_version';
    public const FILTERS_LOCATION = 'location';
    public const FILTERS_EMAIL = 'email';
    public const FILTERS_COUNTRY = 'country';
    public const FILTERS_HOURS_AGO = 'hours_ago';
    public const FILTERS_RADIUS = 'radius';
    public const FILTERS_LAT = 'lat';
    public const FILTERS_LONG = 'long';

    public const TAGS = 'tags'; // deprecated
    public const TAGS_KEY = self::FILTERS_KEY; // deprecated
    public const TAGS_RELATION = self::FILTERS_RELATION; // deprecated
    public const TAGS_VALUE = self::FILTERS_VALUE; // deprecated

    // Send to Specific Devices
    public const INCLUDE_PLAYER_IDS = 'include_player_ids';
    public const INCLUDE_EXTERNAL_USER_IDS = 'include_external_user_ids';
    public const INCLUDE_EMAIL_TOKENS = 'include_email_tokens';
    public const INCLUDE_PHONE_NUMBERS = 'include_phone_numbers';
    public const INCLUDE_IOS_TOKENS = 'include_ios_tokens';
    public const INCLUDE_WP_WNS_URIS = 'include_wp_wns_uris';
    public const INCLUDE_AMAZON_REG_IDS = 'include_amazon_reg_ids';
    public const INCLUDE_CHROME_REG_IDS = 'include_chrome_reg_ids';
    public const INCLUDE_CHROME_WEB_REG_IDS = 'include_chrome_web_reg_ids';
    public const INCLUDE_ANDROID_REG_IDS = 'include_android_reg_ids';

    // Idempotency
    public const EXTERNAL_ID = 'external_id';

    // Content & Language
    public const CONTENTS = 'contents';
    public const HEADINGS = 'headings';
    public const SUBTITLE = 'subtitle'; // iOS 10+
    public const TEMPLATE_ID = 'template_id';
    public const CONTENT_AVAILABLE = 'content_available'; // iOS only
    public const MUTABLE_CONTENT = 'mutable_content'; // iOS 10+
    public const TARGET_CONTENT_IDENTIFIER = 'target_content_identifier'; // iOS 13+

    // Email Content
    public const EMAIL_SUBJECT = 'email_subject';
    public const EMAIL_BODY = 'email_body';
    public const EMAIL_FROM_NAME = 'email_from_name';
    public const EMAIL_FROM_ADDRESS = 'email_from_address';
    public const EMAIL_PREHEADER = 'email_preheader';
    public const DISABLE_EMAIL_CLICK_TRACKING = 'disable_email_click_tracking';

    // SMS
    public const SMS_FROM = 'sms_from';
    public const SMS_MEDIA_URLS = 'sms_media_urls';

    // Attachments
    public const DATA = 'data';
    public const HUAWEI_MSG_TYPE = 'huawei_msg_type';
    public const HUAWEI_MSG_TYPE_DATA = 'data';
    public const HUAWEI_MSG_TYPE_MESSAGE = 'message';
    public const URL = 'url';
    public const WEB_URL = 'web_url'; // ALL BROWSERS
    public const APP_URL = 'app_url'; // ALL APPS
    public const IOS_ATTACHMENTS = 'ios_attachments'; // iOS 10+
    public const BIG_PICTURE = 'big_picture'; // Android only
    public const HUAWEI_BIG_PICTURE = 'huawei_big_picture';
    public const CHROME_WEB_IMAGE = 'chrome_web_image';
    public const ADM_BIG_PICTURE = 'adm_big_picture'; // Amazon only
    public const CHROME_BIG_PICTURE = 'chrome_big_picture'; // Chrome App only

    // Action Buttons
    public const BUTTONS = 'buttons';
    public const WEB_BUTTONS = 'web_buttons'; // CHROME 48+
    public const IOS_CATEGORY = 'ios_category'; // iOS
    public const ICON_TYPE = 'icon_type'; // iOS

    // Appearance
    public const ANDROID_CHANNEL_ID = 'android_channel_id'; // ANDROID
    public const HUAWEI_CHANNEL_ID = 'huawei_channel_id';
    public const EXISTING_ANDROID_CHANNEL_ID = 'existing_android_channel_id'; // ANDROID
    public const HUAWEI_EXISTING_CHANNEL_ID = 'huawei_existing_channel_id'; // ANDROID
    public const ANDROID_BACKGROUND_LAYOUT = 'android_background_layout'; // ANDROID
    public const SMALL_ICON = 'small_icon'; // ANDROID
    public const HUAWEI_SMALL_ICON = 'huawei_small_icon'; // ANDROID
    public const LARGE_ICON = 'large_icon'; // ANDROID
    public const HUAWEI_LARGE_ICON = 'huawei_large_icon'; // ANDROID
    public const ADM_SMALL_ICON = 'adm_small_icon'; // ANDROID
    public const ADM_LARGE_ICON = 'adm_large_icon'; // ANDROID
    public const CHROME_WEB_ICON = 'chrome_web_icon'; // ANDROID
    public const CHROME_WEB_BADGE = 'chrome_web_badge'; // ANDROID
    public const FIREFOX_ICON = 'firefox_icon';
    public const CHROME_ICON = 'chrome_icon';
    // ...
    public const IOS_SOUND = 'ios_sound'; // iOS
    public const IOS_SOUND_NIL = 'nil'; // iOS
    public const ANDROID_SOUND = 'android_sound'; // ANDROID
    public const ANDROID_SOUND_NIL = 'notification'; // ANDROID
    public const ANDROID_LED_COLOR = 'android_led_color'; // ANDROID
    public const ANDROID_ACCENT_COLOR = 'android_accent_color'; // ANDROID
    public const HUAWEI_ACCENT_COLOR = 'huawei_accent_color'; // ANDROID
    public const ANDROID_VISIBILITY = 'android_visibility'; // ANDROID 5.0+
    public const IOS_BADGE_TYPE = 'ios_badgeType'; // iOS
    public const IOS_BADGE_COUNT = 'ios_badgeCount'; // iOS
    // This is known as APNS-collapse-id on iOS 10+ and collapse_key on Android.
    public const COLLAPSE_ID = 'collapse_id';
    public const WEB_PUSH_TOPIC = 'web_push_topic';
    public const APNS_ALERT = 'apns_alert'; // iOS 10+

    // Delivery
    public const SEND_AFTER = 'send_after';
    public const DELAYED_OPTION = 'delayed_option';
    public const DELAYED_OPTION_TIMEZONE = 'timezone';
    public const DELAYED_OPTION_LAST_ACTIVE = 'last-active';
    public const DELIVERY_TIME_OF_DAY = 'delivery_time_of_day'; // Example: "9:00AM"
    public const TTL = 'ttl'; // iOS, ANDROID, CHROME, SAFARI, CHROMEWEB
    public const PRIORITY = 'priority'; // ANDROID, CHROME, CHROMEWEB
    public const PRIORITY_LOW = 5;
    public const PRIORITY_HIGH = 10;
    public const APNS_PUSH_TYPE_OVERRIDE = 'apns_push_type_override'; // iOS
    public const ENABLE_FREQUENCY_CAP = 'enable_frequency_cap';

    // Throttling
    public const THROTTLE_RATE_PER_MINUTE = 'throttle_rate_per_minute';
    public const THROTTLE_RATE_PER_MINUTE_DISABLE = 0;

    // Grouping & Collapsing
    public const ANDROID_GROUP = 'android_group'; // ANDROID
    public const ANDROID_GROUP_MESSAGE = 'android_group_message'; // ANDROID
    public const ADM_GROUP = 'adm_group'; // ANDROID
    public const ADM_GROUP_MESSAGE = 'adm_group_message'; // ANDROID
    public const THREAD_ID = 'thread_id'; // iOS 12+
    public const SUMMARY_ARG = 'summary_arg'; // iOS 12+
    public const SUMMARY_ARG_COUNT = 'summary_arg_count'; // iOS 12+
    public const IOS_RELEVANCE_SCORE = 'ios_relevance_score'; // iOS 15+
    public const IOS_INTERRUPTION_LEVEL = 'ios_interruption_level'; // iOS 15+

    // Platform to Deliver To
    public const IS_IOS = 'isIos';
    public const IS_ANDROID = 'isAndroid';
    public const IS_HUAWEI = 'isHuawei';
    public const IS_ANY_WEB = 'isAnyWeb';
    public const IS_CHROME_WEB = 'isChromeWeb';
    public const IS_FIREFOX = 'isFirefox';
    public const IS_SAFARI = 'isSafari';
    public const IS_WP_WNS = 'isWP_WNS';
    public const IS_ADM = 'isAdm';
    public const IS_CHROME = 'isChrome';
    public const CHANNEL_FOR_EXTERNAL_USER_IDS = 'channel_for_external_user_ids';

    // WTF???
    public const ANDROID_BACKGROUND_DATA = 'android_background_data';
}
