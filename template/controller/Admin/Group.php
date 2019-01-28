<?php


namespace Controller\Admin;

use \Waiterphp\Admin\Config\AdminTree;
use Waiterphp\Core\DB;

class Group extends \Controller\Base
{

    protected $adminConfigs = [];

    public function __construct()
    {
        parent::__construct();

        // 树
        $adminTree = new AdminTree($this->model('adminGroup'));
        $adminTree->addNodeAction('add')->setName('添加');
        $adminTree->addNodeAction('delete')->setName('删除');

        $this->adminConfigs[] = $adminTree;
    }

    protected function activeConfig()
    {
        $index = $this->request->getInt('index', 0);
        return $this->adminConfigs[$index];
    }

    protected function activeDao()
    {
        $config = $this->activeConfig();
        return call_user_func([$config, 'getDao']);
    }

    public function getConfigs()
    {
        return array_map(function($config){
            return call_user_func(array($config, 'getConfig'));
        }, $this->adminConfigs);
    }

    public function getNodes()
    {
        $nodeId = $this->request->getInt('nodeId', 0);
        $nodes = $this->activeDao()->where(array('parentId'=>$nodeId))->getList();
        return $nodes;
    }

    public function addNode()
    {
        $label = $this->request->getString('label');
        $parentId = $this->request->getInt('parentId', 0);
        $preNodeId = $this->request->getInt('preNodeId', 0);
        $nextNodeId = $this->request->getInt('nextNodeId', 0);
        $_this = $this;
        $nodeId = DB::transaction(function() use ($_this, $label, $parentId, $preNodeId, $nextNodeId) {
            $nodeId = $this->activeDao()->insert(array('label'=>$label));
            $_this->pushNode($nodeId, $parentId, $preNodeId, $nextNodeId);
            return $nodeId;
        });
        return $nodeId;
    }

    public function changeNodLabel()
    {
        $nodeId = $this->request->getInt('nodeId');
        $label = $this->request->getString('label');
        return $this->activeDao()->updateField($nodeId, 'label', $label);
    }



    public function deleteNode()
    {
        $nodeId = $this->request->getInt('nodeId');
        $_this = $this;
        DB::transaction(function() use ($_this, $nodeId) {
            $_this->popNode($nodeId);
            $_this->activeDao()->deleteById($nodeId);
        });
        return true;
    }

    public function changeNodePosition()
    {
        $nodeId = $this->request->getInt('nodeId');
        $parentId = $this->request->getInt('parentId', 0);
        $preNodeId = $this->request->getInt('preNodeId', 0);
        $nextNodeId = $this->request->getInt('nextNodeId', 0);
        $_this = $this;
        DB::transaction(function() use ($_this, $nodeId, $parentId, $preNodeId, $nextNodeId) {
            $_this->popNode($nodeId);
            $_this->pushNode($nodeId, $parentId, $preNodeId, $nextNodeId);
        });
        return true;
    }


    private function pushNode($nodeId, $parentId, $preNodeId, $nextNodeId)
    {
        $update = array('parentId'=>$parentId);
        if (!empty($preNodeId)) {
            $this->activeDao()->updateById($preNodeId, array(
                'nextNodeId'=>$nodeId,
            ));
            $update['preNodeId'] = $preNodeId;
        }
        if (!empty($nextNodeId)) {
            $this->activeDao()->updateById($nextNodeId, array(
                'preNodeId'=>$nodeId,
            ));
            $update['nextNodeId'] = $nextNodeId;
        }
        $this->activeDao()->updateById($nodeId, $update);
    }

    private function popNode($nodeId)
    {
        $info = $this->activeDao()->infoById($nodeId);
        if ($info['preNodeId'] > 0) {
            $this->activeDao()->updateField($info['preNodeId'], 'nextNodeId', $info['nextNodeId']);
        }
        if ($info['nextNodeId'] > 0) {
            $this->activeDao()->updateField($info['nextNodeId'], 'preNodeId', $info['preNodeId']);
        }
    }

}