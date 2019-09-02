<?php
/**
 * Created by PhpStorm.
 * User: mingzhanghui
 * Date: 8/29/2019
 * Time: 15:34
 */
class Curl
{
    /**
     * @param $map
     * @return string
     */
    public static function buildQuery(/* array */ $map) {
        $a = [];
        array_walk($map, function($value, $name) use (&$a) {
            array_push($a, sprintf("%s=%s", urlencode($name), urlencode($value)));
        });
        return implode('&', $a);
    }

    /**
     * https://www.ft12.com
     * @param $u
     * @return mixed
     * @throws Exception
     */
    public static function shortURL($u) {
        $api = "http://api.ft12.com/api.php";
        return self::get($api, [
            'url' => $u,
            'apikey' => 'b9ctQmGAxH5OMctHY0@ddd',
        ]);
    }

    public static function testURL($u) {
        $pat = '/^http(s)?:\/\/.*$/';
        preg_match($pat, $u, $matches);
        if (!$matches) {
            $u = "http://".$u;
        }
        $api = "http://qbview.url.cn/getResourceInfo";
        return self::get($api, [
            'appid' => 31,
            'url' => $u
        ]);
    }

    /**
     * www.kancloud.cn => https://www.kancloud.cn
     * @param $host
     * @return mixed
     * @throws Exception
     */
    public static function host2URL($host) {
        // host本来就是 http:// https://直接返回
        $pat = '/^http(s)?:\/\/.*$/';
        preg_match($pat, $host, $matches);
        if ($matches) {
            return $host;
        }
        // 尝试http://
        $url = "http://".$host;

        $ch = curl_init();
        $headers = [
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.142 Safari/537.36 OPR/62.0.3331.116",
            "Host: ".$host,
            "Connection: keep-alive"
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 5
        ]);

        $data = curl_exec($ch);
        $errno = curl_errno($ch);
        if ($errno) {
            throw new Exception(curl_error($ch), $errno);
        }
        curl_close($ch);

        // 提取http响应码
        // "HTTP/1.1 301 Moved Permanently"
        // "HTTP/1.1 200 OK"
        $header1stLine = self::subStrByDelim($data);
        $a = explode(' ', $header1stLine);
        $statusCode = intval($a[1]);
        // 重定向 提取Http Header Location: xxx中的URL
        if (300 <= $statusCode && $statusCode < 400) {
            $hs = self::extractHttpHeader($data);
            $headerAssoc = self::getHeaderAssoc($hs);
            if (isset($headerAssoc['Location'])) {
                return $headerAssoc['Location'];
            }
            return "https://".$host;
        }
        return $url;
    }

    private static function getHeaderAssoc($header) {
        $a = explode("\r\n", $header);
        $assoc = [];
        for ($i = 1; $i < count($a); $i++) {
            $b = explode(": ", $a[$i]);
            $assoc[ $b[0] ] = $b[1];
        }
        return $assoc;
    }

    private static function extractHttpHeader($s, $delim = "\r\n\r\n") {
        $n = strlen($delim);
        $j = 0;
        for ($i = 0; isset($s[$i]) && $j < $n; $i++) {
            if ($delim[$j] === $s[$i]) {
                $j++;
            } else {
                $j = 0;
            }
        }
        return substr($s, 0, $i-$n);
    }

    private static function subStrByDelim($s, $delim="\r") {
        $x = 0;
        $i = 0;
        for (; isset($s[$i]); $i++) {
            if ($s[$i] === $delim) {
                $x = $i;
                break;
            }
        }
        if (!isset($s[$i])) {return $s;}
        return substr($s, 0, $x);
    }

    /**
     * @param $host string
     * @param $params array assoc
     * @return mixed
     * @throws Exception
     */
    protected static function get($host, $params) {
        $ch = curl_init();
        $headers = [
            "Accept: application/json",
            "X-Requested-with: XMLHttpRequest",
        ];
        $qs = self::buildQuery($params);
        $fullURL = sprintf("%s?%s", $host, $qs);

        curl_setopt_array($ch, [
            CURLOPT_URL => $fullURL,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 5
        ]);

        $data = curl_exec($ch);
        $errno = curl_errno($ch);
        if ($errno) {
            throw new Exception("%s\n", curl_error($ch), $errno);
        }
        curl_close($ch);
        return $data;
    }

}
