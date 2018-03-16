<?php

/*
 * *【网页】***************************
 * 提供模板渲染机制和page分离化基类*
 *************************************
 *
 * 使用方法：
 *
 * 注意：
 * ----使用twig模板引擎
 *
 */

abstract class View
{
    private static $instance = array();

    public static function register(array $configs, $viewName = 'default')
    {
        assertOrException(!isset(self::$instance[$viewName]), 'view is set:' . $viewName);
        assertOrException(isset($configs['type']), 'view type not set');
        $drives = 'view.' . $configs['type'] . '.render';
        return self::$instance[$viewName] = Drives::load($drives, $configs);
    }

    public static function get($viewName = 'default')
    {
        assertOrException(isset(self::$instance[$viewName]), 'view is not set:' . $viewName);
        return self::$instance[$viewName];
    }

    protected $config = array();

    abstract public function __construct($config);

    public function getConfig()
    {
        return $this->config;
    }

    abstract public function fetch($template, array $params);

    public function render($template, $params)
    {
        echo $this->fetch($template, $params);
    }
}




