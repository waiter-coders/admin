<?php
namespace Waiterphp\Admin\Tests;

use Waiterphp\Admin\TableTrait as TableTrait;
use \Waiterphp\Admin\Config\Table as TableConfig;

use \Waiterphp\Core\Filter\FilterTrait as FilterTrait;

class TestTableTrait extends TestCase
{
    private $controller;
    private $request;

    protected function SetUp()
    {
        parent::SetUp();
        $this->controller = new Table();
        $this->request = new Request([

        ]);
    }

    public function test_getConfig()
    {
        $config = $this->controller->getConfig($this->request);
        $this->assertTrue(!empty($config));
    }

    public function test_getList()
    {
        $list = $this->controller->getList($this->request);var_dump($list);
        $this->assertTrue(!empty($list));
    }
}

class Table
{
    use TableTrait;

    protected function setConfig()
    {
        return new TableConfig(new ExamChoiceModel());
    }

}

use Waiterphp\Core\Dao\DaoTrait;

class ExamChoiceModel
{
    use DaoTrait;
    protected function setDaoConfig()
    {
        // 数据源配置
        $this->daoConfig->setTable('exam_choice');
        $this->daoConfig->setPrimaryKey('choiceId');
        $this->daoConfig->setField('examId', 'number', '');
        $this->daoConfig->setField('checkpointId', 'number', '');
        $this->daoConfig->setField('stem', 'string', '');
        $this->daoConfig->setField('choice_A', 'string', '');
        $this->daoConfig->setField('choice_B', 'string', '');
        $this->daoConfig->setField('choice_C', 'string', '');
        $this->daoConfig->setField('choice_D', 'string', '');
        $this->daoConfig->setField('answer', 'number', '');
        $this->daoConfig->setField('analysis', 'string', '');
    }
}


class Request
{
    use FilterTrait;

    public function __construct($data)
    {
        $this->setData($data);
    }

}