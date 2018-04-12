<?php
namespace Controller\Record;

class SimpleList extends \Controller\AdminBase
{
    private $dao;
    private $adminList;

    public function __construct()
    {
        # 父类构造函数
        parent::__construct();

        assertOrException($this->hasPower(), 'not has power');

        # 数据模型
        $this->dao = $this->model('record');

        # 配置工具
        $this->adminList = new \AdminList($this->dao);
        $this->adminList->addPublicAction('add');
        $this->adminList->addItemAction('edit');
        $this->adminList->addItemAction('delete');
        $this->adminList->addItemAction('showTitle')->setName('标题')->setAjax()->setDisabled(function ($record) {
            return false;
        });
        $this->adminList->addItemAction('test')->setUrl('delete?@data.primaryKey=@data.id');
        $this->adminList->addItemAction('test_1')->setUrl('../SimpleEditor/show?@data.primaryKey=@data.id');
        $this->adminList->addItemAction('test_2')->setUrl('/Tree/Simple/show?@data.primaryKey=@data.id');
        $this->adminList->addItemAction('test_3')->setUrl('http://git.oschina.net/waiterall/waiterphp');
    }

    public function show()
    {
        $this->render('record/simpleList.html', $this->adminList->getConfig());
    }


    public function getConfig()
    {
        return array( $this->adminList->getConfig());
    }

    public function getList()
    {

    }

    public function totalNum()
    {

    }

    public function action()
    {
        $action = $this->post->getString('action');
        assertOrException(!empty($action) && method_exists($this, $action), 'action error');
        $ids = $this->post->getArray('ids', array());
        $data = $this->post->getArray('data', array());
        return call_user_func(array($this, $action), $ids, $data);
    }

    public function fieldUpdate()
    {
        $primaryKey = $this->dao->primaryKey();
        $id = $this->post->getInt($primaryKey);
        $field = $this->post->getString('field');
        $content = $this->post->getString('content');
        $result = $this->dao->updateById($id, array(
            $field=>$content,
        ));
        return $result;
    }

    public function delete()
    {
        $primaryKey = $this->dao->primaryKey();
        $id = $this->request->getInt($primaryKey);
        $delNum = $this->dao->delete($id);
        assertOrException($delNum > 0, '删除失败');
        return true;
    }

    public function showTitle()
    {
        $primaryKey = $this->dao->primaryKey();
        $id = $this->request->getInt($primaryKey);
        return $id;
    }
}