<?php
namespace Waiterphp\Admin;

trait TreeTrait
{
    use BaseTrait;

    public function getTree($request)
    {
        $topicId = $request->getInt($this->dao->getKeyName('topicId'));
        $nodeId = $request->getInt($this->dao->getKeyName('nodeId'), 0);
        $nodes = $this->dao->getTree($topicId, $nodeId);
        return $nodes;
    }

    public function addNode($request)
    {
        $topicId = $request->getInt($this->dao->getKeyName('topicId'));
        $label = $request->getString($this->dao->getKeyName('label'));
        $moveToId = $request->getInt('moveToId', 0);
        $moveType = $request->getString('moveType', 'after');
        $nodeId = $this->dao->addNode($topicId, $label, $moveToId, $moveType);
        return $nodeId;
    }

    public function changeNodeLabel($request)
    {
        $topicId = $request->getInt($this->dao->getKeyName('topicId'));
        $nodeId = $request->getInt($this->dao->getKeyName('nodeId'));
        $label = $request->getString($this->dao->getKeyName('label'));
        return $this->dao->changeNodeLabel($topicId, $nodeId, $label);
    }



    public function deleteNode($request)
    {
        $topicId = $request->getInt($this->dao->getKeyName('topicId'));
        $nodeId = $request->getInt($this->dao->getKeyName('nodeId'));
        return $this->dao->deleteNode($topicId, $nodeId);
    }

    public function changeNodePosition($request)
    {
        $topicId = $request->getInt($this->dao->getKeyName('topicId'));
        $nodeId = $request->getInt($this->dao->getKeyName('nodeId'));
        $moveToId = $request->getInt('moveToId');
        $moveType = $request->getString('moveType');
        return $this->dao->changeNodePosition($topicId, $nodeId, $moveToId, $moveType);
    }
}