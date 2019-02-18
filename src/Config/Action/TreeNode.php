<?php
namespace Waiterphp\Admin\Config\Action;


class TreeNode extends Base
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
            'edit'=>['type'=>'page', 'name'=>'编辑', 'url'=>'edit'],
            'delete'=>['type'=>'ajax', 'name'=>'删除',  'confirm'=>'您确定要删除@data.id@吗？', 'url'=>'/delete/?@primaryKey@=@data.id@'],
        ];
    }
}