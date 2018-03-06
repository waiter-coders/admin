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
        return version_compare(self::$version, $compareVersion, '>=');
    }

    public static function versionIsEmpty()
    {
        return empty(self::$version);
    }
}