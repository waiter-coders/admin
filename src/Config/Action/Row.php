<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/16
 * Time: 13:59
 */

namespace Waiterphp\Admin\Config\Action;


class Row extends Base
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
        return [
            'edit'=>['type'=>'page', 'name'=>'编辑'],
            'delete'=>[
                'type'=>'ajax', 'name'=>'删除',
                'confirm'=>'您确定要删除@data.id@吗？',
                'url'=>'delete/?@primaryKey@=@data.id@'
            ],
            'audit'=>['type'=>'ajax', 'name'=>'审核',  'url'=>'audit'],
        ];
    }

}