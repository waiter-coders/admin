<?php

namespace Waiterphp\Admin\Config;


class AdminTree extends AdminBase
{
    private $type = 'admin-tree';

    private $nodeActionsOrder = array();
    private $nodeActionsMap = array();

    public function __construct($dao)
    {
        parent::__construct($dao);
    }

    public function addNodeAction($action)
    {
        if (!isset($this->nodeActionsMap[$action])) {
            $this->nodeActionsMap[$action] = new Action\TreeNode($action);
            $this->nodeActionsOrder[] = $action;
        }
        return $this->nodeActionsMap[$action];
    }

    public function getConfig()
    {
        $config =  array('type'=>$this->type);
        $config['primaryKey'] = $this->dao->primaryKey();

        // 处理节点操作
        if (!empty($this->nodeActionsOrder)) {
            foreach($this->nodeActionsOrder as $action) {
                $config['tableActions'][] = call_user_func(array($this->nodeActionsMap[$action], 'getConfig'));
            }
        }

        return $config;
    }
}