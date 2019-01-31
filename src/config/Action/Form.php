<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/16
 * Time: 13:59
 */

namespace Waiterphp\Admin\Config\Action;


class Form extends Base
{
    public function __construct($action)
    {
        parent::__construct($action);
        $default = self::defaultRowActions();
        if (isset($default[$action])) {
            $this->config = array_merge($this->config, $default[$action]);
        }
    }

    private static function defaultRowActions()
    {
        return array(
            'edit'=>array('type'=>'ajax', 'name'=>'编辑', 'url'=>'formSubmit?@primaryKey@=@data.id@'),
            'add'=>array('type'=>'ajax', 'name'=>'添加',  'url'=>'formSubmit'),
        );
    }

}