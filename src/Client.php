<?php

namespace Mingalevme\OneSignal;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Mingalevme\OneSignal\Exception;
use Mingalevme\OneSignal\Exception\BadRequest;
use Mingalevme\OneSignal\Exception\InvalidPlayerIds;
use Mingalevme\OneSignal\Exception\AllIncludedPlayersAreNotSubscribed;

class Client implements LoggerAwareInterface
{
    const APP_ID = 'app_id';
    
    const GET       = 'get';
    const POST      = 'post';
    const PUT       = 'put';
    const DELETE    = 'delete';
    
    const BASE_URL  = 'https://onesignal.com/api/v1';

    const CONTENTS  = 'contents';
    const HEADINGS  = 'headings';
    const SUBTITLE  = 'subtitle'; // iOS 10+

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
    
    const READ_BLOCK_SIZE = 4096;

    protected $appId;
    protected $restAPIKey;
    
    protected $csvDownloadingTimeout = 30;

    protected $toClose = [];
    protected $toUnlink = [];

    /**
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     *
     * @param string $appId
     * @param string $restAPIKey
     */
    public function __construct($appId, $restAPIKey)
    {
        $this->appId = $appId;
        $this->restAPIKey = $restAPIKey;
        $this->logger = new \Psr\Log\NullLogger();
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

        $tags = [];
        
        foreach ((array) $whereTags as $key => $value) {
            $tags["{$key}={$value}"] = [
                'key' => $key,
                'relation' => '=',
                'value' => $value,
            ];
        }

        if ($tags) {
            if (isset($data[self::TAGS])) {
                $data[self::TAGS] = array_merge(array_values($tags), $data[self::TAGS]);
            } else {
                $data[self::TAGS] = array_values($tags);
            }
        }
        
        // You must include which players, segments, or tags you wish to send this notification to
        if (empty($data[self::INCLUDE_PLAYER_IDS]) && empty($data[self::INCLUDED_SEGMENTS]) && empty($data[self::TAGS])) {
            $data[self::INCLUDED_SEGMENTS] = self::SEGMENTS_ALL;
        }
        
        $data[self::APP_ID] = $this->appId;
        
        $url = self::BASE_URL . '/notifications';
        
        $respone = $this->request(self::POST, $url, $data);
        
        if (isset($respone['errors']['invalid_player_ids'])) {
            throw new InvalidPlayerIds($respone['errors']['invalid_player_ids']);
        } elseif (isset($respone['recipients']) && $respone['recipients'] === 0) {
            throw new AllIncludedPlayersAreNotSubscribed();
        }
        
        return $respone;
    }
    
    /**
     * View the details of multiple devices in one of your OneSignal apps
     * 
     * @param type $limit
     * @param type $offset
     */
    public function getPlayers($limit = null, $offset = null)
    {
        $data = [
            'app_id' => $this->appId,
            'limit' => $limit,
            'offset' => $offset,
        ];
        
        $url = self::BASE_URL . '/players?' . \http_build_query($data);
        
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
     * @param string $tmpdir Directory for tempfile, default is sys_get_temp_dir()
     * @return string URL to compressed CSV export of all of your current user data
     * @throws \Exception if something went wrong
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
            throw new \Exception('Unexpected error while requesting an players/csv_export');
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
            throw new \Exception("Unexpected error while reading csv-file {$fcsv}");
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
        
        $responseRaw = curl_exec($ch);
        
        $info = curl_getinfo($ch);
        
        curl_close($ch);
        
        try {
            $response = \json_decode($responseRaw, true);
        } catch (\Exception $e) {
            $response = null;
        }
        
        if ($response === null) {
            throw new Exception('Unexpected response ' . \json_encode([
                'http-status-code' => $info['http_code'],
                'response' => $responseRaw,
            ]));
        }
        
        if ($info['http_code'] !== 200) {
            throw new BadRequest($response);
        }
        
        return $response;
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
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
