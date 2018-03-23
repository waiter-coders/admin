<?php
namespace Controller\Record;

class SimpleList extends \Controller\AdminBase
{
    private $dao;
    private $config;

    public function __construct()
    {
        parent::__construct();
        // 数据源配置
        $this->dao = $this->model('record');
        $this->config = $this->generateConfig($this->dao);
    }

    public function show()
    {
        $this->render('', $this->config);
    }


    public function getConfig()
    {
        return $this->config;
    }

    public function getList()
    {

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

    private function generateConfig($dao)
    {
        $config = new \AdminList($dao);
        $config->addPublicAction('add');
        $config->addItemAction('edit');
        $config->addItemAction('delete');
        $config->addItemAction('showTitle')->setName('标题')->setAjax()->setDisabled(function ($record) {
            return false;
        });
        $config->addItemAction('test')->setUrl('delete?@data.primaryKey=@data.id');
        $config->addItemAction('test_1')->setUrl('../SimpleEditor/show?@data.primaryKey=@data.id');
        $config->addItemAction('test_2')->setUrl('/Tree/Simple/show?@data.primaryKey=@data.id');
        $config->addItemAction('test_3')->setUrl('http://git.oschina.net/waiterall/waiterphp');
        return $config->getParams();
    }


}