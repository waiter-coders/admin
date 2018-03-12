<?php
namespace \Controller\Record;

class Paging extends \Controller\AdminBase
{
    private $dao;
    private $paging;

    public function __construct()
    {
        parent::__construct();
        // 数据源配置
        $this->dao = $this->model('sample');

        // 列表配置
        $this->paging = $this->paging($this->dao);
    }

    public function show()
    {
        $config = $this->getConfig();
        $this->render('', $config);
    }


    public function getConfig()
    {
        $this->paging->addPublicAction('add');
        $this->paging->addItemAction('edit');
        $this->paging->addItemAction('delete');
        $this->paging->addItemAction('showTitle')->setName('标题')->setAjax()->setDisabled(function($record){
            return false;
        });
        $this->paging->addItemAction('test')->setUrl('delete?@data.primaryKey=@data.id');
        $this->paging->addItemAction('test')->setUrl('../SimpleEditor/show?@data.primaryKey=@data.id');
        $this->paging->addItemAction('test')->setUrl('/Tree/Simple/show?@data.primaryKey=@data.id');
        $this->paging->addItemAction('test')->setUrl('http://git.oschina.net/waiterall/waiterphp');
        $this->paging->setPageSize(15);
        $this->paging->setPageNum($this->request->getInt('page', 1));
        return $this->paging->getParams();
    }

    public function getData()
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



}