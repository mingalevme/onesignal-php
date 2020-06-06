<?php

namespace Mingalevme\OneSignal;

use Mingalevme\OneSignal\Exception\AllIncludedPlayersAreNotSubscribed;
use Mingalevme\OneSignal\Exception\BadRequest;
use Mingalevme\OneSignal\Exception\BadResponse;
use Mingalevme\OneSignal\Exception\InvalidPlayerIds;
use Mingalevme\OneSignal\Exception\ServerError;
use Mingalevme\OneSignal\Exception\ServiceUnavailable;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Client implements LoggerAwareInterface
{
    const BASE_URL = 'https://onesignal.com/api/v1';

    // Send to Segments
    const INCLUDED_SEGMENTS = 'included_segments';
    const SEGMENTS_ALL = 'All';
    const EXCLUDED_SEGMENTS = 'excluded_segments';

    // Send to Users Based on Filters
    const FILTERS = 'filters';
    const FILTERS_FIELD = 'field';
    const FILTERS_RELATION = 'relation';
    const FILTERS_VALUE = 'value';
    const FILTERS_TAG_KEY = 'key';
    const FILTERS_LAST_SESSION = 'last_session';
    const FILTERS_FIRST_SESSION = 'first_session';
    const FILTERS_SESSION_COUNT = 'session_count';
    const FILTERS_SESSION_TIME = 'session_time';
    const FILTERS_AMOUNT_SPENT = 'amount_spent';
    const FILTERS_BOUGHT_SKU = 'bought_sku';
    const FILTERS_TAG = 'tag';
    const FILTERS_LANGUAGE = 'language';
    const FILTERS_APP_VERSION = 'app_version';
    const FILTERS_LOCATION = 'location';
    const FILTERS_EMAIL = 'email';
    const FILTERS_COUNTRY = 'country';

    const TAGS = 'tags'; // deprecated
    const TAGS_KEY = self::FILTERS_TAG_KEY; // deprecated
    const TAGS_RELATION = self::FILTERS_RELATION; // deprecated
    const TAGS_VALUE = self::FILTERS_VALUE; // deprecated

    // Send to Specific Devices
    const INCLUDE_PLAYER_IDS = 'include_player_ids';
    const INCLUDE_EXTERNAL_USER_IDS = 'include_external_user_ids';
    const INCLUDE_EMAIL_TOKENS = 'include_email_tokens';
    const INCLUDE_IOS_TOKENS = 'include_ios_tokens';
    const INCLUDE_WP_WNS_URIS = 'include_wp_wns_uris';
    const INCLUDE_AMAZON_REG_IDS = 'include_amazon_reg_ids';
    const INCLUDE_CHROME_REG_IDS = 'include_chrome_reg_ids';
    const INCLUDE_CHROME_WEB_REG_IDS = 'include_chrome_web_reg_ids';
    const INCLUDE_ANDROID_REG_IDS = 'include_android_reg_ids';

    // App
    const APP_ID = 'app_id';

    // Idempotency
    const EXTERNAL_ID = 'external_id';

    // Content & Language
    const CONTENTS = 'contents';
    const HEADINGS = 'headings';
    const SUBTITLE = 'subtitle'; // iOS 10+
    const TEMPLATE_ID = 'template_id';
    const CONTENT_AVAILABLE = 'content_available'; // iOS only
    const MUTABLE_CONTENT = 'mutable_content'; // iOS 10+

    // Email Content
    // ...

    // Attachments
    const DATA = 'data';
    const URL = 'url';
    const WEB_URL = 'web_url'; // ALL BROWSERS
    const APP_URL = 'app_url'; // ALL APPS
    const IOS_ATTACHMENTS = 'ios_attachments'; // iOS 10+
    const BIG_PICTURE = 'big_picture'; // Android only
    const ADM_BIG_PICTURE = 'adm_big_picture'; // Amazon only
    const CHROME_BIG_PICTURE = 'chrome_big_picture'; // Chrome App only

    // Action Buttons
    const BUTTONS = 'buttons';
    const WEB_BUTTONS = 'web_buttons'; // CHROME 48+
    const IOS_CATEGORY = 'ios_category'; // iOS

    // Appearance
    const ANDROID_CHANNEL_ID = 'android_channel_id'; // ANDROID
    const EXISTING_ANDROID_CHANNEL_ID = 'existing_android_channel_id'; // ANDROID
    const ANDROID_BACKGROUND_LAYOUT = 'android_background_layout'; // ANDROID
    const SMALL_ICON = 'small_icon'; // ANDROID
    const LARGE_ICON = 'large_icon'; // ANDROID
    // ...
    const IOS_SOUND = 'ios_sound'; // iOS
    const IOS_SOUND_NIL = 'nil'; // iOS
    const ANDROID_SOUND = 'android_sound'; // ANDROID
    const ANDROID_SOUND_NIL = 'notification'; // ANDROID
    const ANDROID_LED_COLOR = 'android_led_color'; // ANDROID
    const ANDROID_ACCENT_COLOR = 'android_accent_color'; // ANDROID
    const ANDROID_VISIBILITY = 'android_visibility'; // ANDROID 5.0+
    const IOS_BADGE_TYPE = 'ios_badgeType'; // iOS
    const IOS_BADGE_COUNT = 'ios_badgeCount'; // iOS
    const COLLAPSE_ID = 'collapse_id'; // This is known as APNS-collapse-id on iOS 10+ and collapse_key on Android.
    const APNS_ALERT = 'apns_alert'; // iOS 10+

    // Delivery
    const SEND_AFTER = 'send_after';
    const DELAYED_OPTION = 'delayed_option';
    const DELAYED_OPTION_TIMEZONE = 'timezone';
    const DELAYED_OPTION_LAST_ACTIVE = 'last-active';
    const DELIVERY_TIME_OF_DAY = 'delivery_time_of_day'; // Example: "9:00AM"
    const TTL = 'ttl'; // iOS, ANDROID, CHROME, SAFARI, CHROMEWEB
    const PRIORITY = 'priority'; // ANDROID, CHROME, CHROMEWEB
    const PRIORITY_LOW = 5;
    const PRIORITY_HIGH = 10;
    const APNS_PUSH_TYPE_OVERRIDE = 'apns_push_type_override'; // iOS

    // Grouping & Collapsing
    const ANDROID_GROUP = 'android_group'; // ANDROID
    const ANDROID_GROUP_MESSAGE = 'android_group_message'; // ANDROID
    const THREAD_ID = 'thread_id'; // iOS 12+
    const SUMMARY_ARG = 'summary_arg'; // iOS 12+
    const SUMMARY_ARG_COUNT = 'summary_arg_count'; // iOS 12+

    // Platform to Deliver To
    CONST IS_IOS = 'isIos';
    const IS_ANDROID = 'isAndroid';
    const IS_ANY_WEB = 'isAnyWeb';
    const IS_EMAIL = 'isEmail';
    const IS_CHROME_WEB = 'isChromeWeb';
    const IS_FIREFOX = 'isFirefox';
    const IS_SAFARI = 'isSafari';
    const IS_WP_WNS = 'isWP_WNS';
    const IS_ADM = 'isAdm';
    const IS_CHROME = 'isChrome';
    const CHANNEL_FOR_EXTERNAL_USER_IDS = 'channel_for_external_user_ids';

    // WTF???
    const ANDROID_BACKGROUND_DATA = 'android_background_data';

    //
    const GET = 'get';
    const POST = 'post';
    const PUT = 'put';
    const DELETE = 'delete';

    const READ_BLOCK_SIZE = 4096;
    const OPTION_CURL_OPTIONS = 'curl_opts';

    // -------

    protected $appId;
    protected $restAPIKey;

    protected $csvDownloadingTimeout = 30;

    protected $toClose = [];
    protected $toUnlink = [];

    /** @var array */
    protected $options;

    /** @var LoggerInterface */
    protected $logger;

    /**
     *
     * @param string $appId
     * @param string $restAPIKey
     * @param array|null $options
     */
    public function __construct($appId, $restAPIKey, array $options = null)
    {
        $this->appId = $appId;
        $this->restAPIKey = $restAPIKey;
        $this->logger = new NullLogger();
        $this->options = (array) $options;

        if (array_key_exists(self::OPTION_CURL_OPTIONS, $this->options)) {
            if (!is_array($this->options[self::OPTION_CURL_OPTIONS])) {
                throw new \RuntimeException('Curl options must be an array');
            }
        }
    }

    /**
     * @param string|null $title
     * @param array|null $payload
     * @param array|null $whereTags
     * @param array|null $extra
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

        $data = [];

        if (count($content) > 0) {
            $data[self::CONTENTS] = $content;
        }

        if ($payload) {
            $data[self::DATA] = (array) $payload;
        }

        if ($extra) {
            $data = array_merge($data, (array) $extra);
        }

        if (count($content) > 0 && (isset($content['en']) === false || is_string($content['en']) === false || trim($content['en']) === '')) {
            throw new Exception('Invalid or missing default text of notification (content["en"])');
        }

        if (!isset($data[self::FILTERS])) {
            $data[self::FILTERS] = [];
        }

        $tags = [];

        foreach ((array) $whereTags as $key => $value) {
            $tags["{$key}={$value}"] = [
                'key' => $key,
                'relation' => '=',
                'value' => $value,
            ];
        }

        $tags = array_values($tags);

        if (!empty($data[self::TAGS])) {
            $tags = array_merge($tags, $data[self::TAGS]);
        }

        unset($data[self::TAGS]);

        foreach ($tags as $tag) {
            $data[self::FILTERS][] = [
                'field' => 'tag',
            ] + $tag;
        }

        // You must include which players, segments, or tags you wish to send this notification to
        if (empty($data[self::INCLUDE_PLAYER_IDS]) && empty($data[self::INCLUDED_SEGMENTS]) && empty($data[self::TAGS])) {
            $data[self::INCLUDED_SEGMENTS] = self::SEGMENTS_ALL;
        }

        $data[self::APP_ID] = $this->appId;

        $url = self::BASE_URL . '/notifications';

        $response = $this->request(self::POST, $url, $data);

        if (isset($response['errors']['invalid_player_ids'])) {
            throw new InvalidPlayerIds($response['errors']['invalid_player_ids']);
        } elseif (isset($response['recipients']) && $response['recipients'] === 0) {
            throw new AllIncludedPlayersAreNotSubscribed();
        }

        return $response;
    }

    /**
     * View the details of multiple devices in one of your OneSignal apps
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getPlayers($limit=null, $offset=null)
    {
        $data = [
            'app_id' => $this->appId,
            'limit' => $limit,
            'offset' => $offset,
        ];

        $url = self::BASE_URL . '/players?' . \http_build_query($data);

        /** @noinspection PhpUnnecessaryLocalVariableInspection https://gist.github.com/discordier/ed4b9cba14652e7212f5 */
        $response = $this->request(self::GET, $url);

        return $response;
    }

    /**
     * Returns all players via View devices (/players) method
     * (Danger!) Unavailable for Apps > 100,000 users
     *
     * @return array
     */
    public function getAllPlayersViaPlayers()
    {
        $limit = 299;

        $result = [];

        while (true) {
            $players = $this->getPlayers($limit + 1, count($result))['players'];
            $result = array_merge($result, $players);
            if (count($players) <= $limit) {
                break;
            }
        }

        return $result;
    }

    /**
     * Returns a URL to compressed CSV export of all of your current user data
     *
     * @param string $extra Additional fields that you wish to include. Currently supports location, country, and rooted.
     * @return string URL to compressed CSV export of all of your current user data
     */
    public function export($extra = null)
    {
        $url = self::BASE_URL . '/players/csv_export';

        $data = [self::APP_ID => $this->appId];

        if ($extra) {
            $data['extra_fields'] = (array) $extra;
        }

        $response = $this->request(self::POST, $url, $data);

        if ((bool) $response === false || isset($response['csv_file_url']) === false) {
            throw new Exception('Unexpected error while requesting an players/csv_export');
        }

        return $response['csv_file_url'];
    }

    public function getNextPlayerViaExport($extra = null, $tmpdir = null, $timeout = null)
    {
        $gzCsvUrl = $this->export($extra);
        $fgz = ($tmpdir ? $tmpdir : sys_get_temp_dir()) . "/onesignal-players-{$this->appId}-" . date('Y-m-d-H-i-s') . '.csv.gz';
        $fcsv = str_replace('.csv.gz', '.csv', $fgz);

        $this->downloadCsv(is_null($timeout) ? $this->csvDownloadingTimeout : $timeout, $gzCsvUrl, $fgz);

        $this->ungzip($fgz, $fcsv);

        unlink($fgz);

        $csvhandle = fopen($fcsv, 'r');

        $this->toClose[$fcsv] = $csvhandle;
        $this->toUnlink[$fcsv] = $fcsv;

        $keys = fgetcsv($csvhandle);

        if ((bool) $keys === false) {
            throw new Exception("Unexpected error while reading csv-file {$fcsv}");
        }

        while (($line = fgetcsv($csvhandle)) !== false) {
            $player = [];
            foreach ($keys as $i => $key) {
                $player[$key] = $line[$i];
            }
            yield $player;
        }

        fclose($csvhandle);
        unset($this->toClose[$fcsv]);

        unlink($fcsv);
        unset($this->toUnlink[$fcsv]);
    }

    /**
     * Returns all players via CSV Export (/csv_export) Method
     *
     * @param array $extra Additional fields that you wish to include. Currently supports location, country, and rooted.
     * @param string $tmpdir This dir is used to download remote gz-file and to unpack it to raw csv-file, default is sys_get_temp_dir()
     * @return array
     * @throws \Exception
     */
    public function getAllPlayersViaExport($extra = null, $tmpdir = null, $timeout = null)
    {
        $players = [];

        foreach($this->getNextPlayerViaExport($extra, $tmpdir, $timeout) as $player) {
            $players[] = $player;
        }

        return $players;
    }

    /**
     * Download a remote resource to a local file
     *
     * @param int $timeout
     * @param string $src Source
     * @param string $dest Destination
     * @return boolean
     * @throws Exception
     */
    protected function downloadCsv($timeout, $src, $dest)
    {
        // sleep(1); // fixes: ErrorException: gzopen(https://...): failed to open stream: HTTP request failed! HTTP/1.1 403 Forbidden

        $start = time();

        while ($timeout === 0 || time() - $start < $timeout) {
            $fp = fopen($dest, 'w');

            $ch = curl_init($src);

            curl_setopt($ch, \CURLOPT_FILE, $fp);
            curl_setopt($ch, \CURLOPT_FOLLOWLOCATION, true);
            curl_exec($ch);

            $info = curl_getinfo($ch);

            curl_close($ch);

            fclose($fp);

            if ($info['http_code'] === 200) {
                return true;
            } else {
                sleep(1);
            }
        }

        throw new Exception(file_get_contents($dest), "Maximum execution time of {$timeout}s exceeded while downloading a remote resource {$src}");
    }

    protected function ungzip($src, $dest)
    {
        $zp = gzopen($src, 'rb');

        $fp = fopen($dest, 'w');

        while (gzeof($zp) === false) {
            $data = gzread($zp, self::READ_BLOCK_SIZE);
            fwrite($fp, $data, strlen($data));
        }

        gzclose($zp);

        fclose($fp);

        return true;
    }

    /**
     * Makes a request to OneSignal
     *
     * @param string $method
     * @param string $url
     * @param array $data
     * @param array $info
     * @return array
     * @throws Exception
     */
    protected function request($method, $url, $data = null, &$info = null)
    {
        $ch = curl_init();

        $headers = [
            "Authorization: Basic {$this->restAPIKey}",
        ];

        curl_setopt($ch, \CURLOPT_URL, $url);
        curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, \CURLOPT_HEADER, false);

        if ($method === self::POST) {
            curl_setopt($ch, \CURLOPT_POST, true);
            $headers[] = "Content-Type: application/json";
            curl_setopt($ch, \CURLOPT_POSTFIELDS, json_encode($data, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES));
        }

        curl_setopt($ch, \CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, \CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, \CURLOPT_TIMEOUT, 15);

        if (!empty($this->options[self::OPTION_CURL_OPTIONS])) {
            foreach ($this->options[self::OPTION_CURL_OPTIONS] as $option => $value) {
                curl_setopt($ch, $option, $value);
            }
        }

        /** @var string $responseBody */
        $start = microtime(true);
        $responseBody = curl_exec($ch);
        $processedIn = round(microtime(true) - $start, 3);

        $error = curl_error($ch);
        $info = self::compressArray(curl_getinfo($ch) + [
            '_processed_in' => $processedIn,
            '_curl_error' => $error ?: null,
        ]);

        curl_close($ch);

        if ($responseBody === false) {
            throw new ServerError($error ?: 'Unknown error', 0, null, $info);
        }

        if ($info['http_code'] === 503) {
            throw new ServiceUnavailable($responseBody, $info);
        } elseif ($info['http_code'] > 500) {
            throw new ServerError(null, $responseBody, $info['http_code'], $info);
        }

        try {
            $responseData = \json_decode($responseBody, true);
        } catch (\Exception $e) {
            $responseData = null;
        }

        if ($responseData === null) {
            throw new BadResponse('Response body is invalid', $responseBody, $info['http_code'], $info);
        }

        if ($info['http_code'] !== 200) {
            throw new BadRequest($responseData);
        }

        return $responseData;
    }

    public function __destruct()
    {
        foreach ($this->toClose as $fname => $handle) {
            if (is_resource($handle)) {
                try {
                    fclose($handle);
                } catch (\ErrorException $e) {
                    $this->logger->warning("Error while closing resource \"{$fname}\": {$e->getMessage()}");
                }
            }
        }

        foreach ($this->toUnlink as $fname) {
            if (is_file($fname)) {
                try {
                    unlink($fname);
                } catch (\ErrorException $e) {
                    $this->logger->warning("Error while removing file \"{$fname}\": {$e->getMessage()}");
                }
            }
        }
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     * @return void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param array $arr
     * @return array
     */
    protected static function compressArray($arr)
    {
        return array_filter((array) $arr, function ($value) {
            return boolval($value);
        });
    }
}
