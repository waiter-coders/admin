<?php

class Curl
{
    public static function post($url, $post, $header = array())
    {
        return self::sendRequest($url, $post, 'post', $header);
    }

    public static function get($url, $query = array(), $header = array())
    {
        if (!empty($query)) {
            $url .= strpos($url, '?') ? '&' : '?';
            $url .= http_build_query($query);
        }
        return self::sendRequest($url, array(), 'get', $header);
    }

    public static function sendRequest($url, $params = array(), $type = 'get', $header = array())
    {
        $curl = curl_init();
        $header =  empty($header) ? self::defaultHeader($url) : $header;
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        if(stripos($url,"https://")!==FALSE){ // https支持
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        if (!empty($params)) {
            $post = self::formatPost($params);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        }
        $response = self::exec($curl); // 失败自动重发, 三次
        curl_close($curl);
        return $response;
    }

    private static function defaultHeader($url)
    {
        $header = array();
        $header[] = "Host: " . self::host($url);
        $header[] = "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        $header[] = "Connection: keep-alive";
        $header[] = "Keep-Alive: 10";
        $header[] = "Accept-Charset: GB2312,ISO-8859-1,utf-8;q=0.7,*;q=0.7";
        $header[] = "Accept-Language: zh-cn,zh,en-us,en;q=0.5";
        return $header;
    }

    private static function host($url)
    {
        if (strpos($url, 'https://')) {
            return substr($url, 8, strpos($url, '/', 8) - 8);
        } else {
            return substr($url, 7, strpos($url, '/', 7) - 7);
        }
    }

    private static function exec(&$curl)
    {
        $count = 0;
        $response = null;
        while (empty($response) && $count < 3) {
            usleep(1000);
            $response = curl_exec($curl);
            $count++;
        }
        return $response;
    }

    public static function formatPost($post)
    {
        $post = is_array($post) ? http_build_query($post) : $post;
        return $post;
    }
}