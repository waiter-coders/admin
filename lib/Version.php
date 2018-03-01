<?php
class Version
{
    private static $version = '';

    public static function setVersion($version)
    {
        self::$version = is_callable($version) ? $version() : $version;
    }

    public static function current()
    {
        return self::$version;
    }

    public static function newerVersion($compareVersion)
    {
        $currentVersion = explode('.', self::$version);
        $compareVersion = explode('.', $compareVersion);
        foreach ($currentVersion as $pos=>$number) {
            if ($number > $compareVersion[$pos]) {
                return true;
            }
        }
        return false;
    }

    public static function versionIsEmpty()
    {
        return empty(self::$version);
    }
}