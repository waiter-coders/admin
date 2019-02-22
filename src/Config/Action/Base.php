<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/16
 * Time: 13:58
 */

namespace Waiterphp\Admin\Config\Action;

class Base
{
    protected static $typeMap = [
        'page'=>1,
        'ajax'=>2,
    ];

    protected $config = [
        'id'=>'',
        'type'=>'page',
        'isShow'=>true,
        'isDisabled'=>false,
        'url'=>'',
        'name'=>'',
    ];

    public function __construct($action)
    {
        $this->config['id'] = $action;
        $this->config['name'] = $action;
    }

    public function set($key, $value)
    {
        $this->config[$key] = $value;
        return $this;
    }

    public function setDisabled(callable $callback)
    {
        $this->isDisabled = $callback;
        return $this;
    }

    public function setHidden()
    {
        $args = func_get_args();
        if (func_num_args() == 3) {
            $args = [[$args[0], $args[1], $args[2]]];
        } else {
            $args = $args[0];
        }
        $this->config['hidden'] = $args;
        return $this;
    }

    public function setName($name)
    {
        $this->config['name'] = $name;
        return $this;
    }

    public function setUrl($url)
    {
        $this->config['url'] = $url;
        return $this;
    }

    public function setAjax($message = '')
    {
        $this->config['type'] = 'ajax';
        $this->config['message'] = $message;
        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }
}