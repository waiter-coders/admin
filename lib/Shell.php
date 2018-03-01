<?php
class Shell
{
    public static function getArg($key)
    {
        $arg = getopt($key.':');
        return isset($arg[$key]) ? $arg[$key] : false;
    }

    public static function isCmd()
    {
        return (PHP_SAPI == 'cli');
    }

    public static function getInput($message)
    {
        echo $message . "\r\n";
        $input = trim(fgets(STDIN));
        if ($input == 'exit') {
            exit();
        }
        return $input;
    }
}
