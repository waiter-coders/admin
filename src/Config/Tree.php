<?php

namespace Waiterphp\Admin\Config;


class Tree extends Base
{
    protected $type = 'AdminTree';

    private $nodeActionsOrder = [];
    private $nodeActionsMap = [];

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
        $config =  ['type'=>$this->type];
        $config['primaryKey'] = $this->dao->primaryKey();
        $config['treeKeys'] = $this->dao->getTreeKeys();

        // 处理节点操作
        if (!empty($this->nodeActionsOrder)) {
            foreach($this->nodeActionsOrder as $action) {
                $config['tableActions'][] = call_user_func([$this->nodeActionsMap[$action], 'getConfig']);
            }
        }

        return $config;
    }
}