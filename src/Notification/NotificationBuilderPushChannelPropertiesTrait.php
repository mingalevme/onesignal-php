<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal\Notification;

use InvalidArgumentException;
use Mingalevme\OneSignal\CreateNotificationOptions as CNO;

/**
 * @mixin NotificationBuilder
 */
trait NotificationBuilderPushChannelPropertiesTrait
{
    // Push Channel Properties / Push Notification Content

    /**
     * @param non-empty-string|non-empty-array<non-empty-string, non-empty-string> $title
     * @return $this
     */
    public function setTitle($title): self
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
     * @param non-empty-string|non-empty-array<non-empty-string, non-empty-string> $text
     * @return $this
     */
    public function setText($text): self
    {
        return $this->setLocalizedText(CNO::CONTENTS, $text);
    }

    /**
     * @param non-empty-string $attributeName
     * @param non-empty-string|non-empty-array<non-empty-string, non-empty-string> $text
     * @return $this
     */
    private function setLocalizedText(string $attributeName, $text): self
    {
        if (is_string($text)) {
            $this->data[$attributeName] = [
                'en' => $text,
            ];
            return $this;
        }

        if (empty($text['en'])) {
            throw new InvalidArgumentException('Invalid or missing default text of notification (content["en"])');
        }

        $this->data[$attributeName] = $text;

        return $this;
    }

    /**
     * @param non-empty-string $value
     * @return $this
     */
    public function setTemplateId(string $value): self
    {
        $this->data[CNO::TEMPLATE_ID] = $value;
        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setContentAvailable(bool $value): self
    {
        $this->data[CNO::CONTENT_AVAILABLE] = $value;
        return $this;
    }

    /**
     * @param non-empty-string $value
     * @return $this
     */
    public function setTargetContentIdentifier(string $value): self
    {
        $this->data[CNO::TARGET_CONTENT_IDENTIFIER] = $value;
        return $this;
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
        $this->data[CNO::DATA] = $value;
        return $this;
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
        $this->data[CNO::HUAWEI_MSG_TYPE] = $value;
        return $this;
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
        $this->data[CNO::URL] = $value;
        return $this;
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
        $this->data[CNO::WEB_URL] = $value;
        return $this;
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
        $this->data[CNO::APP_URL] = $value;
        return $this;
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
     * @param non-empty-array<string, mixed> $value
     * @return $this
     */
    public function setIosAttachments(array $value): self
    {
        $this->data[CNO::IOS_ATTACHMENTS] = $value;
        return $this;
    }

    /**
     * Picture to display in the expanded view. Can be a drawable resource name or a URL.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setBigPicture(string $value): self
    {
        $this->data[CNO::BIG_PICTURE] = $value;
        return $this;
    }

    /**
     * Picture to display in the expanded view. Can be a drawable resource name or a URL.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setHuaweiBigPicture(string $value): self
    {
        $this->data[CNO::HUAWEI_BIG_PICTURE] = $value;
        return $this;
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
        $this->data[CNO::CHROME_WEB_IMAGE] = $value;
        return $this;
    }

    /**
     * Picture to display in the expanded view. Can be a drawable resource name or a URL.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setAdmBigPicture(string $value): self
    {
        $this->data[CNO::ADM_BIG_PICTURE] = $value;
        return $this;
    }

    /**
     * Large picture to display below the notification text. Must be a local URL.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setChromeBigPicture(string $value): self
    {
        $this->data[CNO::CHROME_BIG_PICTURE] = $value;
        return $this;
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
     * @param non-empty-list<array{id: non-empty-string, text: non-empty-string, icon?: non-empty-string}> $value
     * @return $this
     */
    public function setButtons(array $value): self
    {
        $this->data[CNO::BUTTONS] = $value;
        return $this;
    }

    /**
     * Add action buttons to the notification. The id field is required.
     *
     * Example: [
     *  {"id": "like-button", "text": "Like", "icon": "http://i.imgur.com/N8SN8ZS.png", "url": "https://yoursite.com"},
     *  {"id": "read-more-button", "text": "Read more", "icon": "http://i.imgur.com/MIxJp1L.png", "url": "https://yoursite.com"}
     * ]
     *
     * @param non-empty-list<array{id: non-empty-string, text?: non-empty-string, icon?: non-empty-string, url?: non-empty-string}> $value
     * @return $this
     */
    public function setWebButtons(array $value): self
    {
        $this->data[CNO::WEB_BUTTONS] = $value;
        return $this;
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
        $this->data[CNO::IOS_CATEGORY] = $value;
        return $this;
    }

    /**
     * In iOS you can specify the type of icon to be used in an Action button as being either ['system', 'custom']
     *
     * @param non-empty-string $value
     * @psalm-param 'system'|'custom' $value
     * @phpstan-param 'system'|'custom' $value
     * @return $this
     */
    public function setIconType(string $value): self
    {
        $this->data[CNO::ICON_TYPE] = $value;
        return $this;
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
        $this->data[CNO::ANDROID_CHANNEL_ID] = $value;
        return $this;
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
        $this->data[CNO::HUAWEI_CHANNEL_ID] = $value;
        return $this;
    }

    /**
     * Use this if you have client side Android Oreo Channels you have already defined in your app with code.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setExistingAndroidChannelId(string $value): self
    {
        $this->data[CNO::EXISTING_ANDROID_CHANNEL_ID] = $value;
        return $this;
    }

    /**
     * Use this if you have client side Android Oreo Channels you have already defined in your app with code.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setHuaweiExistingChannelId(string $value): self
    {
        $this->data[CNO::HUAWEI_EXISTING_CHANNEL_ID] = $value;
        return $this;
    }

    /**
     * @deprecated Deprecated, this field doesn't work on Android 12+
     *
     * Allowing setting a background image for the notification. This is a JSON object containing the following keys.
     * See our Background Image documentation for image sizes:
     *  https://documentation.onesignal.com/docs/android-customizations#section-background-images
     *
     * @param array{image: non-empty-string, headings_color: non-empty-string, contents_color: non-empty-string} $value
     * @return $this
     */
    public function setAndroidBackgroundLayout(array $value): self
    {
        $this->data[CNO::ANDROID_BACKGROUND_LAYOUT] = $value;
        return $this;
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
        $this->data[CNO::SMALL_ICON] = $value;
        return $this;
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
        $this->data[CNO::HUAWEI_SMALL_ICON] = $value;
        return $this;
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
        $this->data[CNO::LARGE_ICON] = $value;
        return $this;
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
        $this->data[CNO::HUAWEI_LARGE_ICON] = $value;
        return $this;
    }

    /**
     * If not set a bell icon will be used or ic_stat_onesignal_default if you have set this resource name.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setAdmSmallIcon(string $value): self
    {
        $this->data[CNO::ADM_SMALL_ICON] = $value;
        return $this;
    }

    /**
     * If blank the small_icon is used. Can be a drawable resource name or a URL.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setAdmLargeIcon(string $value): self
    {
        $this->data[CNO::ADM_LARGE_ICON] = $value;
        return $this;
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
        $this->data[CNO::CHROME_WEB_ICON] = $value;
        return $this;
    }

    /**
     * Sets the web push notification icon for Android devices in the notification shade. Please see
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setChromeWebBadge(string $value): self
    {
        $this->data[CNO::CHROME_WEB_BADGE] = $value;
        return $this;
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
        $this->data[CNO::FIREFOX_ICON] = $value;
        return $this;
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
        $this->data[CNO::CHROME_ICON] = $value;
        return $this;
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
        $this->data[CNO::IOS_SOUND] = $value;
        return $this;
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
        $this->data[CNO::ANDROID_ACCENT_COLOR] = $value;
        return $this;
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
        $this->data[CNO::HUAWEI_ACCENT_COLOR] = $value;
        return $this;
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
        $this->data[CNO::IOS_BADGE_TYPE] = $value;
        return $this;
    }

    /**
     * Used with ios_badgeType, describes the value to set or amount to increase/decrease your app's iOS badge count by.
     *
     * You can use a negative number to decrease the badge count when used with an ios_badgeType of Increase.
     *
     * @param int $value
     * @return $this
     */
    public function setIosBadgeCount(int $value): self
    {
        $this->data[CNO::IOS_BADGE_COUNT] = $value;
        return $this;
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
        $this->data[CNO::APNS_ALERT] = $value;
        return $this;
    }

    // Grouping & Collapsing
}
