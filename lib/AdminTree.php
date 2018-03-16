<?php
abstract class AdminTree extends AdminBase
{
    protected $daoConfig;
    protected $tree;
    protected $dao;

    public function __construct()
    {
        // 初始化基本配置
        $this->daoConfig = Dao::newConfig();
        $this->tree = new TreeConfig($this->daoConfig);

        // 装载子类配置
        $this->load();

        // 把dao配置转化为dao对象
        $this->dao = new Dao($this->daoConfig);

        // 赋值公共变量
        parent::__construct();
    }

    abstract protected function load();

    public function pageHome()
    {
        $root = $this->dao->getAll(array(
            'parentId'=>0,
        ));
        $config = $this->tree->getConfigs();
        $this->assign('config', $config);
        $this->assign('config_json', json_encode($config));
        $this->assign('tree_json', json_encode($root));
        $this->display('layout/tree.html');
    }

    public function getNodes()
    {
        $id = Request::getInt('id');
        $nodes = $this->dao->getAll(array(
            'parentId'=>$id,
        ));
        $this->response($nodes);
    }

    public function update()
    {
        $name = $this->request->getString('name');
        $id = $this->request->getInt($this->daoConfig->primaryKey);
        $this->dao->updateField($id, 'name', $name);
        $this->response(true);
    }

    public function add()
    {
        $name = $this->request->getString('name');
        $parentId = $this->request->getInt('parentId', 0);
        $isSub = $this->request->getInt('isSub');
        $insert = array(
            'name'=>$name,
            'parentId'=>0,
            'layer'=>0,
            'trace'=>array(),
            'isLeaf'=>1,
        );
        if ($parentId != 0) {
            $info = $this->dao->infoById($parentId);
            $insert['parentId'] = ($isSub) ? $parentId : $info['parentId'];
            $insert['layer'] = ($isSub) ? $info['layer'] + 1 : $info['layer'];
            $insert['trace'] = ($isSub) ? $info['trace'] : array_slice($info['trace'], 0 , -1);
        }
        $insert[$this->daoConfig->primaryKey] = $this->dao->insert($insert);
        if ($isSub) {
            $this->dao->updateField($parentId, 'isLeaf', 0);
        }
        $this->response($insert);
    }

    public function delete()
    {
        $id = $this->request->getInt('id');
        $this->dao->delete($id);
        $this->response(true);
    }

    protected function setDefaultFields()
    {
        $this->daoConfig->setField('parentId', 'int', '父类');
        $this->daoConfig->setField('trace', 'json', '轨迹');
        $this->daoConfig->setField('name', 'varchar', 50, '分类名');
        $this->daoConfig->setField('layer', 'int', '层级');
        $this->daoConfig->setField('isLeaf', 'int', '是否为子叶');
    }
}

