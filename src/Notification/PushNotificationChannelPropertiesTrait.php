<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal\Notification;

use InvalidArgumentException;
use Mingalevme\OneSignal\CreateNotificationOptions as CNO;

/**
 * @mixin AbstractNotification
 */
trait PushNotificationChannelPropertiesTrait
{
    // Push Channel Properties / Push Notification Content

    /**
     * @param non-empty-string|non-empty-array<non-empty-string, non-empty-string> $text
     * @return $this
     */
    public function setContents($text): self
    {
        return $this->setLocalizedText(CNO::CONTENTS, $text);
    }

    /**
     * @param non-empty-string|non-empty-array<non-empty-string, non-empty-string> $title
     * @return $this
     */
    public function setHeadings($title): self
    {
        return $this->setLocalizedText(CNO::HEADINGS, $title);
    }

    /**
     * @param non-empty-string|non-empty-array<non-empty-string, non-empty-string> $subtitle
     * @return $this
     */
    public function setSubtitle($subtitle): self
    {
        return $this->setLocalizedText(CNO::SUBTITLE, $subtitle);
    }

    /**
     * Use a template you set up on our dashboard.
     *
     * The template_id is the UUID found in the URL when viewing a template on our dashboard.
     *
     * Example: be4a8044-bbd6-11e4-a581-000c2940e62c
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setTemplateId(string $value): self
    {
        return $this->setAttribute(CNO::TEMPLATE_ID, $value);
    }

    /**
     * Sending true wakes your app from background to run custom native code
     *  (Apple interprets this as content-available=1).
     *
     * Note: Not applicable if the app is in the "force-quit" state (i.e app was swiped away).
     * Omit the contents field to prevent displaying a visible notification.
     *
     * @param bool $value
     * @return $this
     */
    public function setContentAvailable(bool $value): self
    {
        return $this->setAttribute(CNO::CONTENT_AVAILABLE, $value);
    }

    /**
     *
     * Always defaults to true and cannot be turned off.
     *
     * Allows tracking of notification receives and changing of the notification content in your app
     *  before it is displayed. Triggers didReceive(_:withContentHandler:) on your UNNotificationServiceExtension:
     *  https://developer.apple.com/documentation/usernotifications/unnotificationserviceextension
     *
     * iOS 10+
     *
     * @return $this
     */
    public function setMutableContent(): self
    {
        return $this->setAttribute(CNO::MUTABLE_CONTENT, true);
    }

    /**
     * Use to target a specific experience in your App Clip, or to target your notification to a specific window
     *  in a multi-scene App:
     *  https://documentation.onesignal.com/docs/app-clip-support
     *
     * iOS 13+
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setTargetContentIdentifier(string $value): self
    {
        return $this->setAttribute(CNO::TARGET_CONTENT_IDENTIFIER, $value);
    }

    // Push Channel Properties / Attachments

    /**
     * A custom map of data that is passed back to your app. Same as using Additional Data within the dashboard.
     * Can use up to 2048 bytes of data.
     *
     * @see https://documentation.onesignal.com/docs/sending-notifications#advanced-settings
     *
     * Example: {"abc": 123, "foo": "bar", "event_performed": true, "amount": 12.1}
     *
     * @param array<non-empty-string, mixed> $value
     * @return $this
     */
    public function setData(array $value): self
    {
        return $this->setAttribute(CNO::DATA, $value);
    }

    /**
     * Use "data" or "message" depending on the type of notification you are sending.
     * More details in Data & Background Notifications: https://documentation.onesignal.com/docs/data-notifications
     *
     * @param non-empty-string $value
     * @psalm-param 'data'|'message' $value
     * @phpstan-param 'data'|'message' $value
     * @return $this
     */
    public function setHuaweiMsgType(string $value): self
    {
        return $this->setAttribute(CNO::HUAWEI_MSG_TYPE, $value);
    }

    /**
     * The URL to open in the browser when a user clicks on the notification.
     *
     * Example: https://onesignal.com
     *
     * Note: iOS needs https or updated NSAppTransportSecurity in plist
     *
     * This field supports tag substitutions.
     *
     * Omit if including *web_url* or *app_url*
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setUrl(string $value): self
    {
        return $this->setAttribute(CNO::URL, $value);
    }

    /**
     * Same as url but only sent to web push platforms.
     * Including Chrome, Firefox, Safari, Opera, etc.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setWebUrl(string $value): self
    {
        return $this->setAttribute(CNO::WEB_URL, $value);
    }

    /**
     * Same as url but only sent to app platforms.
     * Including iOS, Android, macOS, Windows, ChromeApps, etc.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setAppUrl(string $value): self
    {
        return $this->setAttribute(CNO::APP_URL, $value);
    }

    /**
     * Adds media attachments to notifications.
     * Set as JSON object, key as a media id of your choice and the value as a valid local filename or URL.
     * User must press and hold on the notification to view.
     *
     * Do not set mutable_content to download attachments. The OneSignal SDK does this automatically.
     *
     * Example: {"id1": "https://domain.com/image.jpg"}
     *
     * iOS 10+
     *
     * @param non-empty-array<non-empty-string, non-empty-string> $value
     * @return $this
     */
    public function setIosAttachments(array $value): self
    {
        return $this->setAttribute(CNO::IOS_ATTACHMENTS, $value);
    }

    /**
     * Picture to display in the expanded view. Can be a drawable resource name or a URL.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setBigPicture(string $value): self
    {
        return $this->setAttribute(CNO::BIG_PICTURE, $value);
    }

    /**
     * Picture to display in the expanded view. Can be a drawable resource name or a URL.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setHuaweiBigPicture(string $value): self
    {
        return $this->setAttribute(CNO::HUAWEI_BIG_PICTURE, $value);
    }

    /**
     * Sets the web push notification's large image to be shown below the notification's title and text.
     * Please see Web Push Notification Icons: https://documentation.onesignal.com/docs/web-push-notification-icons
     *
     * Works for Chrome for Windows & Android only. Chrome for macOS uses the same image set for the chrome_web_icon.
     *
     * Chrome 56+
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setChromeWebImage(string $value): self
    {
        return $this->setAttribute(CNO::CHROME_WEB_IMAGE, $value);
    }

    /**
     * Picture to display in the expanded view. Can be a drawable resource name or a URL.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setAdmBigPicture(string $value): self
    {
        return $this->setAttribute(CNO::ADM_BIG_PICTURE, $value);
    }

    /**
     * Large picture to display below the notification text. Must be a local URL.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setChromeBigPicture(string $value): self
    {
        return $this->setAttribute(CNO::CHROME_BIG_PICTURE, $value);
    }

    // Push Channel Properties / Action Buttons

    /*
     * These add buttons to push notifications, allowing the user to take more than one action on a notification.
     * Learn more about Action Buttons: https://documentation.onesignal.com/docs/action-buttons
     */

    /**
     * Buttons to add to the notification. Icon only works for Android.
     * Buttons show in order of array position.
     *
     * Example:
     *  [
     *      {"id": "id1", "text": "first button", "icon": "ic_menu_share"},
     *      {"id": "id2", "text": "second button", "icon": "ic_menu_send"}
     *  ]
     *
     * iOS 8.0+, Android 4.1+, and derivatives like Amazon
     *
     * @param non-empty-list<ActionButton> $value
     * @return $this
     */
    public function setButtons(array $value): self
    {
        $data = array_map(fn(ActionButton $button) => $button->toOneSignalActionButton(), $value);
        return $this->setAttribute(CNO::BUTTONS, $data);
    }

    /**
     * Add action buttons to the notification. The id field is required.
     *
     * Example: [
     *  {"id": "like-button", "text": "Like", "icon": "http://i.imgur.com/N8SN8ZS.png", "url": "https://yoursite.com"},
     *  {"id": "read-more-button", "text": "Read more", "icon": "http://i.com/MIxJp1L.png", "url": "https://site.com"}
     * ]
     *
     * @param non-empty-list<WebActionButton> $value
     * @return $this
     */
    public function setWebButtons(array $value): self
    {
        $data = array_map(fn(WebActionButton $button) => $button->toOneSignalActionButton(), $value);
        return $this->setAttribute(CNO::WEB_BUTTONS, $data);
    }

    /**
     * Category APS payload, use with registerUserNotificationSettings:categories in your Objective-C / Swift code.
     * https://developer.apple.com/documentation/usernotifications/unnotificationcategory
     *
     * Example: calendar category which contains actions like accept and decline
     *
     * iOS 10+ This will trigger your UNNotificationContentExtension whose ID matches this category.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setIosCategory(string $value): self
    {
        return $this->setAttribute(CNO::IOS_CATEGORY, $value);
    }

    /**
     * In iOS, you can specify the type of icon to be used in an Action button as being either ['system', 'custom']
     *
     * @param non-empty-string $value
     * @psalm-param 'system'|'custom' $value
     * @phpstan-param 'system'|'custom' $value
     * @return $this
     */
    public function setIconType(string $value): self
    {
        return $this->setAttribute(CNO::ICON_TYPE, $value);
    }

    // Push Channel Properties / Appearance

    /**
     * The Android Oreo Notification Category to send the notification under.
     * See the Category documentation on creating one and getting it's id:
     *  https://documentation.onesignal.com/docs/android-notification-categories
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setAndroidChannelId(string $value): self
    {
        return $this->setAttribute(CNO::ANDROID_CHANNEL_ID, $value);
    }

    /**
     * The Android Oreo Notification Category to send the notification under.
     * See the Category documentation on creating one and getting it's id.
     *  https://documentation.onesignal.com/docs/android-notification-categories
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setHuaweiChannelId(string $value): self
    {
        return $this->setAttribute(CNO::HUAWEI_CHANNEL_ID, $value);
    }

    /**
     * Use this if you have client side Android Oreo Channels you have already defined in your app with code.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setExistingAndroidChannelId(string $value): self
    {
        return $this->setAttribute(CNO::EXISTING_ANDROID_CHANNEL_ID, $value);
    }

    /**
     * Use this if you have client side Android Oreo Channels you have already defined in your app with code.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setHuaweiExistingChannelId(string $value): self
    {
        return $this->setAttribute(CNO::HUAWEI_EXISTING_CHANNEL_ID, $value);
    }

    /**
     * Icon shown in the status bar and on the top left of the notification.
     * Set the icon name without the file extension.
     * If not set a bell icon will be used or ic_stat_onesignal_default if you have set this resource name.
     * See: How to create small icons: https://documentation.onesignal.com/docs/customize-notification-icons
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setSmallIcon(string $value): self
    {
        return $this->setAttribute(CNO::SMALL_ICON, $value);
    }

    /**
     * Icon shown in the status bar and on the top left of the notification.
     * Use an Android resource path (E.g. /drawable/small_icon).
     * Defaults to your app icon if not set.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setHuaweiSmallIcon(string $value): self
    {
        return $this->setAttribute(CNO::HUAWEI_SMALL_ICON, $value);
    }

    /**
     * Can be a drawable resource name (exclude file extension) or a URL.
     * See: How to create large icons:
     *  https://documentation.onesignal.com/docs/customize-notification-icons
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setLargeIcon(string $value): self
    {
        return $this->setAttribute(CNO::LARGE_ICON, $value);
    }

    /**
     * Can be a drawable resource name or a URL.
     * See: How to create large icons:
     *  https://documentation.onesignal.com/docs/customize-notification-icons
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setHuaweiLargeIcon(string $value): self
    {
        return $this->setAttribute(CNO::HUAWEI_LARGE_ICON, $value);
    }

    /**
     * If not set a bell icon will be used or ic_stat_onesignal_default if you have set this resource name.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setAdmSmallIcon(string $value): self
    {
        return $this->setAttribute(CNO::ADM_SMALL_ICON, $value);
    }

    /**
     * If blank the small_icon is used. Can be a drawable resource name or a URL.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setAdmLargeIcon(string $value): self
    {
        return $this->setAttribute(CNO::ADM_LARGE_ICON, $value);
    }

    /**
     * Sets the web push notification's icon.
     * An image URL linking to a valid image. Common image types are supported; GIF will not animate.
     * We recommend 256x256 (at least 80x80) to display well on high DPI devices.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setChromeWebIcon(string $value): self
    {
        return $this->setAttribute(CNO::CHROME_WEB_ICON, $value);
    }

    /**
     * Sets the web push notification icon for Android devices in the notification shade. Please see
     *  https://documentation.onesignal.com/docs/web-push-notification-icons#section-badge
     *
     * Chrome 53+ on Android 6.0+
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setChromeWebBadge(string $value): self
    {
        return $this->setAttribute(CNO::CHROME_WEB_BADGE, $value);
    }

    /**
     * Sets the web push notification's icon for Firefox.
     * An image URL linking to a valid image. Common image types are supported; GIF will not animate.
     * We recommend 256x256 (at least 80x80) to display well on high DPI devices.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setFirefoxIcon(string $value): self
    {
        return $this->setAttribute(CNO::FIREFOX_ICON, $value);
    }

    /**
     * This flag is not used for web push For web push, please see chrome_web_icon instead.
     *
     * The local URL to an icon to use. If blank, the app icon will be used.
     *
     * ChromeApp
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setChromeIcon(string $value): self
    {
        return $this->setAttribute(CNO::CHROME_ICON, $value);
    }

    /**
     * Sound file that is included in your app to play instead of the default device notification sound.
     * Pass nil to disable vibration and sound for the notification.
     *
     * Example: "notification.wav"
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setIosSound(string $value): self
    {
        return $this->setAttribute(CNO::IOS_SOUND, $value);
    }

    /**
     * Sets the background color of the notification circle to the left of the notification text.
     *
     * Only applies to apps targeting Android API level 21+ on Android 5.0+ devices.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setAndroidAccentColor(string $value): self
    {
        return $this->setAttribute(CNO::ANDROID_ACCENT_COLOR, $value);
    }

    /**
     * Accent Color used on Action Buttons and Group overflow count.
     * Uses RGB Hex value (E.g. #9900FF).
     * Defaults to device’s theme color if not set.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setHuaweiAccentColor(string $value): self
    {
        return $this->setAttribute(CNO::HUAWEI_ACCENT_COLOR, $value);
    }

    /**
     * Describes whether to set or increase/decrease your app's iOS badge count by the ios_badgeCount specified count.
     *
     * Can specify None, SetTo, or Increase.
     *
     * None leaves the count unaffected.
     *
     * SetTo directly sets the badge count to the number specified in ios_badgeCount.
     *
     * Increase adds the number specified in ios_badgeCount to the total.
     * Use a negative number to decrease the badge count.
     *
     * @param non-empty-string $value
     * @psalm-param 'None'|'SetTo'|'Increase' $value
     * @phpstan-param 'None'|'SetTo'|'Increase' $value
     * @return $this
     */
    public function setIosBadgeType(string $value): self
    {
        return $this->setAttribute(CNO::IOS_BADGE_TYPE, $value);
    }

    /**
     * Used with ios_badgeType, describes the value to set or amount to increase/decrease your app's iOS badge count by.
     *
     * You can use a negative number to decrease the badge count when used with an ios_badgeType of Increase.
     *
     * @param int<0, max> $value
     * @return $this
     */
    public function setIosBadgeCount(int $value): self
    {
        return $this->setAttribute(CNO::IOS_BADGE_COUNT, $value);
    }

    /**
     * iOS can localize push notification messages on the client using special parameters such as loc-key.
     * When using the Create Notification endpoint, you must include these parameters inside of a field
     *  called apns_alert.
     * Please see Apple's guide on localizing push notifications to learn more.
     *
     * @param non-empty-array<non-empty-string, mixed> $value
     * @return $this
     */
    public function setApnsAlert(array $value): self
    {
        return $this->setAttribute(CNO::APNS_ALERT, $value);
    }

    // Grouping & Collapsing

    /**
     * Notifications with the same group will be stacked together using Android's Notification Grouping feature:
     *  https://documentation.onesignal.com/docs/android-customizations#section-notification-grouping
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setAndroidGroup(string $value): self
    {
        return $this->setAttribute(CNO::ANDROID_GROUP, $value);
    }

    /**
     * Summary message to display when 2+ notifications are stacked together. Default is "# new messages".
     * Include $[notif_count] in your message and it will be replaced with the current number.
     *
     * Languages - The value of each key is the message that will be sent to users for that language.
     * "en" (English) is required.
     * The key of each hash is either a 2 character language code or one of zh-Hans/zh-Hant for
     *  Simplified or Traditional Chinese. Read more: supported languages.
     *
     * Example: {"en": "You have $[notif_count] new messages"}
     *
     * Note: This only works for Android 6 and older. Android 7+ allows full expansion of all message.
     *
     * @param non-empty-string|non-empty-array<non-empty-string, non-empty-string> $value
     * @return $this
     */
    public function setAndroidGroupMessage($value): self
    {
        return $this->setLocalizedText(CNO::ANDROID_GROUP_MESSAGE, $value);
    }

    /**
     * Notifications with the same group will be stacked together using Android's Notification Grouping feature:
     *  https://documentation.onesignal.com/docs/android-customizations#section-notification-grouping
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setAdmGroup(string $value): self
    {
        return $this->setAttribute(CNO::ADM_GROUP, $value);
    }

    /**
     * Summary message to display when 2+ notifications are stacked together. Default is "# new messages".
     * Include $[notif_count] in your message and it will be replaced with the current number.
     * "en" (English) is required.
     * The key of each hash is either a 2 character language code or one of zh-Hans/zh-Hant for
     *  Simplified or Traditional Chinese.
     * The value of each key is the message that will be sent to users for that language.
     *
     * Example: {"en": "You have $[notif_count] new messages"}
     *
     * @param non-empty-string|non-empty-array<non-empty-string, non-empty-string> $value
     * @return $this
     */
    public function setAdmGroupMessage($value): self
    {
        return $this->setLocalizedText(CNO::ADM_GROUP_MESSAGE, $value);
    }

    /**
     * Only one notification with the same id will be shown on the device.
     * Use the same id to update an existing notification instead of showing a new one. Limit of 64 characters.
     *
     * iOS 10+, Android
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setCollapseId(string $value): self
    {
        return $this->setAttribute(CNO::COLLAPSE_ID, $value);
    }

    /**
     * Display multiple notifications at once with different topics.
     *
     * Push - All Browsers
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setWebPushTopic(string $value): self
    {
        return $this->setAttribute(CNO::WEB_PUSH_TOPIC, $value);
    }

    /**
     * This parameter is supported in iOS 12 and above. It allows you to group related notifications together.
     *
     * If two notifications have the same thread-id, they will both be added to the same group.
     *
     * iOS 12+
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setThreadId(string $value): self
    {
        return $this->setAttribute(CNO::THREAD_ID, $value);
    }

    /**
     * When using thread_id to create grouped notifications in iOS 12+, you can also control the summary.
     * For example, a grouped notification can say "12 more notifications from John Doe".
     *
     * The summary_arg lets you set the name of the person/thing the notifications are coming from,
     *  and will show up as "X more notifications from summary_arg"
     *
     * iOS 12+
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setSummaryArg(string $value): self
    {
        return $this->setAttribute(CNO::SUMMARY_ARG, $value);
    }

    /**
     * When using thread_id, you can also control the count of the number of notifications in the group.
     *  For example, if the group already has 12 notifications, and you send a new notification with
     *  summary_arg_count = 2, the new total will be 14 and the summary will be "14 more notifications from summary_arg"
     *
     * iOS 12+
     *
     * @param int<1, max> $value
     * @return $this
     */
    public function setSummaryArgCount(int $value): self
    {
        return $this->setAttribute(CNO::SUMMARY_ARG_COUNT, $value);
    }

    /**
     * A iOS: Relevance Score is a score to be set per notification to indicate how it should be displayed when grouped:
     *  https://documentation.onesignal.com/docs/ios-relevance-score
     *
     * iOS 15+
     *
     * @param float $value float<0, 1>
     * @return $this
     */
    public function setIosRelevanceScore(float $value): self
    {
        if ($value < 0 || $value > 1) {
            throw new InvalidArgumentException('Value must be between 0-1');
        }
        return $this->setAttribute(CNO::IOS_RELEVANCE_SCORE, $value);
    }

    /**
     * iOS: Focus Modes and Interruption Levels indicate the priority and delivery timing of a notification,
     *  to ‘interrupt’ the user. Up until iOS 15, Apple primarily focused on Critical notifications:
     *  https://documentation.onesignal.com/docs/ios-focus-modes-and-interruption-levels
     *
     * Can choose from options: ['active', 'passive', 'time_sensitive', 'critical']
     *
     * Default is active.
     *
     * @param non-empty-string $value
     * @psalm-param 'active'|'passive'|'time_sensitive'|'critical' $value
     * @phpstan-param 'active'|'passive'|'time_sensitive'|'critical' $value
     * @return $this
     */
    public function setIosInterruptionLevel(string $value): self
    {
        return $this->setAttribute(CNO::IOS_INTERRUPTION_LEVEL, $value);
    }

    // Delivery

    /**
     * Time To Live - In seconds. The notification will be expired if the device does not come back online within
     *  this time.
     * The default is 259,200 seconds (3 days).
     *
     * Max value to set is 2419200 seconds (28 days).
     *
     * @param int<1, 2419200> $value
     * @return $this
     */
    public function setTtl(int $value): self
    {
        return $this->setAttribute(CNO::TTL, $value);
    }

    /**
     * Delivery priority through the push server (example GCM/FCM).
     * Pass 10 for high priority or any other integer for normal priority.
     * Defaults to normal priority for Android and high for iOS.
     * For Android 6.0+ devices setting priority to high will wake the device out of doze mode.
     *
     * @param int<1, 2419200> $value
     * @return $this
     */
    public function setPriority(int $value): self
    {
        return $this->setAttribute(CNO::PRIORITY, $value);
    }

    /**
     * valid values: "voip"
     * Set the value to "voip" for sending VoIP Notifications:
     *  https://documentation.onesignal.com/docs/voip-notifications
     *
     * This field maps to the APNS header apns-push-type.
     *
     * Note: "alert" and "background" are automatically set by OneSignal
     *
     * iOS
     *
     * @param non-empty-string $value
     * @psalm-param 'voip' $value
     * @phpstan-param 'voip' $value
     * @return $this
     */
    public function setApnsPushTypeOverride(string $value): self
    {
        return $this->setAttribute(CNO::APNS_PUSH_TYPE_OVERRIDE, $value);
    }

    /**
     * When frequency capping is enabled for the app, sending true will apply the frequency capping to the notification.
     * If the parameter is not included, the default behavior is to apply frequency capping if the setting is enabled
     *  for the app.
     * Setting the parameter to false will override the frequency capping, meaning that the notification will be sent
     *  without considering frequency capping.
     *
     * All - Push
     *
     * @param bool $value
     * @return $this
     */
    public function setEnableFrequencyCap(bool $value): self
    {
        return $this->setAttribute(CNO::ENABLE_FREQUENCY_CAP, $value);
    }
}
