<?php
namespace Waiterphp\Admin;

trait TreeTrait
{
    use BaseTrait;

    public function getTree($request)
    {
        $nodeId = $request->getInt('nodeId', 0);
        $nodes = $this->dao->getTree($nodeId);
        return $nodes;
    }

    public function addNode($request)
    {
        $label = $request->getString('label');
        $moveToId = $request->getInt('moveToId', 0);
        $moveType = $request->getString('moveType', 'after');
        $nodeId = $this->dao->addNode($label, $moveToId, $moveType);
        return $nodeId;
    }

    public function changeNodeLabel($request)
    {
        $nodeId = $request->getInt('nodeId');
        $label = $request->getString('label');
        return $this->dao->changeNodeLabel($nodeId, $label);
    }



    public function deleteNode($request)
    {
        $nodeId = $request->getInt('nodeId');
        return $this->dao->deleteNode($nodeId);
    }

    public function changeNodePosition($request)
    {
        $nodeId = $request->getInt('nodeId');
        $moveToId = $request->getInt('moveToId');
        $moveType = $request->getString('moveType');
        return $this->dao->changeNodePosition($nodeId, $moveToId, $moveType);
    }
}