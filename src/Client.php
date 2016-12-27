<?php

namespace Mingalevme\OneSignal;

use Exception;

class Client
{
    const APP_ID = 'app_id';

    const CONTENTS = 'contents';
    const HEADINGS = 'headings';
    const SUBTITLE = 'subtitle'; // iOS 10+

    CONST IS_IOS        = 'isIos';
    const IS_ANDROID    = 'isAndroid';
    const IS_WP         = 'isWP';
    const IS_ADM        = 'isAdm';
    const IS_CHROME     = 'isChrome';
    const IS_CHROME_WEB = 'isChromeWeb';
    const IS_SAFARI     = 'isSafari';
    const IS_ANY_WEB    = 'isAnyWeb';

    const INCLUDED_SEGMENTS = 'included_segments';
    const SEGMENTS_ALL      = 'ALL';
    const EXCLUDED_SEGMENTS = 'excluded_segments';
    const INCLUDE_PLAYER_IDS= 'include_player_ids';
    const INCLUDE_IOS_TOKENS= 'include_ios_tokens';
    // ...
    const DATA = 'data';
    const URL = 'url';
    //...
    const TAGS = 'tags';
    // ...
    const SMALL_ICON = 'small_icon';
    const LARGE_ICON = 'large_icon';
    const ANDROID_SOUND = 'android_sound';
    const ANDROID_SOUND_NIL = 'notification';
    const ANDROID_LED_COLOR = 'android_led_color';
    const ANDROID_ACCENT_COLOR = 'android_accent_color'; //5.0+5.
    const ANDROID_VISIBILITY = 'android_visibility';
    const ANDROID_GROUD = 'android_group';
    const ANDROID_GROUP_MESSAGE = 'android_group_message';
    const ANDROID_BACKGROUND_DATA = 'android_background_data';
    // ...
    const IOS_SOUND = 'ios_sound';
    const IOS_SOUND_NIL = 'nil';
    const IOS_BADGE_TYPE = 'ios_badgeType';
    const IOS_BADGE_COUNT = 'ios_badgeCount';
    const IOS_CATEGORY = 'ios_category';
    const COLLAPSE_ID = 'collapse_id'; // This is known as apns-collapse-id on iOS 10+ and collapse_key on Android.
    const IOS_ATTACHMENTS = 'ios_attachments'; // iOS 10+
    // ...
    const BUTTONS = 'buttons';
    // ...
    const DELAYED_OPTION = 'delayed_option';
    const DELAYED_OPTION_TIMEZONE = 'timezone';
    const DELAYED_OPTION_LAST_ACTIVE = 'last-active';
    const DELIVERY_TIME_OF_DAY = 'delivery_time_of_day'; // Example: "9:00AM"
    // ...
    const TTL = 'ttl';
    const PRIORITY = 'priority';
    

    protected $appId;
    protected $restAPIKey;

    /**
     *
     * @param string $appId
     * @param string $restAPIKey
     */
    public function __construct($appId, $restAPIKey)
    {
        $this->appId = $appId;
        $this->restAPIKey = $restAPIKey;
    }

    /**
     * @param null $title
     * @param null $payload
     * @param null $extra
     * @return mixed|null
     * @throws Exception
     */
    public function send($title=null, $payload=null, $whereTags=null, $extra=null)
    {
        $content = [];

        if (is_string($title)) {
            $content['en'] = $title;
        } elseif (is_array($title)) {
            $content = $title;
        }

        $fields = [];

        if (count($content) > 0) {
            $fields[self::CONTENTS] = $content;
        }

        if ($payload) {
            $fields[self::DATA] = (array) $payload;
        }

        if ($extra) {
            $fields = array_merge($fields, (array) $extra);
        }

        if (count($content) > 0 && (isset($content['en']) === false || is_string($content['en']) === false || trim($content['en']) === '')) {
            throw new Exception('Invalid or missing default text of notification (content["en"])');
        }

        $tags = [];
        
        foreach ((array) $whereTags as $key => $value) {
            $tags["{$key}={$value}"] = [
                'key' => $key,
                'relation' => '=',
                'value' => $value,
            ];
        }

        if ($tags) {
            if (isset($fields[self::TAGS])) {
                $fields[self::TAGS] = array_merge(array_values($tags), $fields[self::TAGS]);
            } else {
                $fields[self::TAGS] = array_values($tags);
            }
        }
        
        // You must include which players, segments, or tags you wish to send this notification to
        if (empty($fields[self::INCLUDE_PLAYER_IDS]) && empty($fields[self::INCLUDED_SEGMENTS]) && empty($fields[self::TAGS])) {
            $fields[self::INCLUDED_SEGMENTS] = self::SEGMENTS_ALL;
        }

        return $this->request('notifications', $fields);
    }

    /**
     * @param string $tmpDir
     * @return array
     * @throws Exception
     */
    public function getUsersDump($tmpDir = '/tmp')
    {
        $result = $this->request('players/csv_export');

        if (!$result || !isset($result['csv_file_url'])) {
            throw new \Exception('csv dump not avalable');
        }

        $csvPath = $tmpDir . '/users.csv';

        $gzHandler  = gzopen($result['csv_file_url'], "rb");
        $csvHandler = fopen($csvPath, "w");

        while (!gzeof($gzHandler)) {
            $string = gzread($gzHandler, 4096);
            fwrite($csvHandler, $string, strlen($string));
        }

        gzclose($gzHandler);
        fclose($csvHandler);

        $csv   = file($csvPath);
        $keys  = [];
        $users = [];

        foreach ($csv as $i => $line) {
            $line = str_getcsv($line);

            if ($i == 0) {
                $keys = $line;
            } else {
                $user = [];
                foreach ($keys as $k => $key) {
                    $user[$key] = $line[$k];
                }

                $users[] = $user;
            }
        }

        return $users;
    }

    /**
     * @param string $action
     * @param array $fields
     * @return mixed|null
     * @throws Exception
     */
    protected function request($action, array $fields = [])
    {
        $ch = curl_init();

        $fields[self::APP_ID] = $this->appId;
        
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/{$action}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Basic {$this->restAPIKey}",
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $responseRaw = curl_exec($ch);
        
        try {
            $response = json_decode($responseRaw, true);
        } catch (\Exception $e) {
            $response = NULL;
        }

        $info = curl_getinfo($ch);

        curl_close($ch);

        if (isset($response['errors'][0]) || $info['http_code'] != 200) {
            throw new Exception(isset($response['errors'][0]) ? $response['errors'][0] : $responseRaw);
        }

        return $response;
    }
}
