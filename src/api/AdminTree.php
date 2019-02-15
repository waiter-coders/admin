<?php
namespace Waiterphp\Admin\Api;

use Tools\Request as Request;

class AdminTree
{
    private $adminConfig;
    private $adminDao;

    public function __construct($adminConfig)
    {
        $this->adminConfig = $adminConfig;
        $this->adminDao = $adminConfig->getDao();
    }

    public function getTree(Request $request)
    {
        $nodeId = $request->getInt('nodeId', 0);
        $nodes = $this->adminDao->getTree($nodeId);
        return $nodes;
    }

    public function addNode(Request $request)
    {
        $label = $request->getString('label');
        $moveToId = $request->getInt('moveToId', 0);
        $moveType = $request->getString('moveType', 'after');
        $nodeId = $this->adminDao->addNode($label, $moveToId, $moveType);
        return $nodeId;
    }

    public function changeNodeLabel(Request $request)
    {
        $nodeId = $request->getInt('nodeId');
        $label = $request->getString('label');
        return $this->adminDao->changeNodeLabel($nodeId, $label);
    }



    public function deleteNode(Request $request)
    {
        $nodeId = $request->getInt('nodeId');
        return $this->adminDao->deleteNode($nodeId);
    }

    public function changeNodePosition(Request $request)
    {
        $nodeId = $request->getInt('nodeId');
        $moveToId = $request->getInt('moveToId');
        $moveType = $request->getString('moveType');
        return $this->adminDao->changeNodePosition($nodeId, $moveToId, $moveType);
    }
}