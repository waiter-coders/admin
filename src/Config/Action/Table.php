<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/16
 * Time: 13:58
 */

namespace Waiterphp\Admin\Config\Action;


class Table extends Base
{
    private $location = 'public';

    public function __construct($action)
    {
        parent::__construct($action);
        $default = self::defaultTableActions();
        if (isset($default[$action])) {
            $this->config = array_merge($this->config, $default[$action]);
        }
    }

    public function needSelectIds()
    {
        $this->location = 'select';
        return $this;
    }

    public function getConfig()
    {
        return array_merge(parent::getConfig(), ['location'=>$this->location]);
    }

    private static function defaultTableActions()
    {
        return [
            'add'=>['name'=>'添加', 'type'=>'page'],
            'fastAdd'=>['name'=>'快速添加', 'type'=>'dialog', 'url'=>'/fastAdd'],
            'batchAdd'=>['name'=>'批量添加', 'type'=>'ajax', 'url'=>'/batchAdd']
        ];
    }

}