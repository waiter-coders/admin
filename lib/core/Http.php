<?php

class Http
{
    public static function protocol()
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    }

    public static function host()
    {
        return rtrim(self::protocol().$_SERVER['HTTP_HOST'], '\\');
    }

    public static function ip()
    {
        $ip = '';
        if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
    }

    public static function path($pos = 0)
    {
        $path = isset($_SERVER['PATH_INFO']) ? ltrim($_SERVER['PATH_INFO'], '/') : '';
        if ($pos == 0) {
            return $path;
        }
        $path = explode('/', $path);
        return $path[$pos];
    }

    public static function query()
    {

    }

    public static function url()
    {
        return rtrim(self::host(). '/'. $_SERVER['SCRIPT_NAME'], '\\');
    }

    public static function refer()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
    }

    public static function redirect($jumpUrl)
    {
        // 没有包含域名的自动包含域名
        if (strncmp ($jumpUrl, 'http', 4)) {
            $jumpUrl = self::url() . '/' . ltrim($jumpUrl, '/');
        }
        ob_end_clean();
        header("Location:" . $jumpUrl);
    }

    public static function isAjax()
    {
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return false;
        }
        $ajaxTab = strtolower($_SERVER['HTTP_X_REQUESTED_WITH']);
        return ($ajaxTab == 'xmlhttprequest') ? true : false;
    }

}