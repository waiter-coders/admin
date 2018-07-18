<?php

// 初始化环境
ini_set('register_global', false);
date_default_timezone_set('Asia/Shanghai'); // 默认为东八区
header("content-type;text/html;charset=utf8"); // 默认字符为utf8
define('APP_PATH', realpath(__DIR__ . '/../'));

// 装载composer的autoload
require APP_PATH . '/vendor/autoload.php';

use \Waiterphp\Core\DB;
use \Waiterphp\Core\DaoTrait;

DB::register(array('default'=>array(
    'host'=>'127.0.0.1',
    'user'=>'root',
    'password'=>'',
    'database'=>'sample',
)));

class Data
{
    use DaoTrait;
    protected function setDaoConfig()
    {
        $this->daoConfig->setTable('admin_info');
        $this->daoConfig->setPrimaryKey('adminId');
        $this->daoConfig->setField('account', 'string', 50, '账号');
        $this->daoConfig->setField('name', 'string', 50, '姓名');
        $this->daoConfig->setField('sex', 'select', '性别', array(1=>'男', 2=>'女'));
        $this->daoConfig->setField('birthday', 'date', '生日');
        $this->daoConfig->setField('platform', 'multiSelect', '所属平台', array(
            1=>'平台A',
            2=>'平台B',
            3=>'平台C',
            4=>'平台D'
        ));
//        $this->daoConfig->setField('city', 'linkSelect');
//        $this->daoConfig->setField('country', 'tinyint', '国家');
//        $this->daoConfig->setField('province', 'tinyint', '省');
//        $this->daoConfig->setField('city', 'tinyint', '市');
        $this->daoConfig->setField('face', 'image', 50, '头像');
//        $this->daoConfig->setField('mobile', 'phone', 11, '手机号');
//        $this->daoConfig->setField('email', 'email', 50, '邮箱');
        $this->daoConfig->setField('addTime', 'datetime', '添加时间');
        $this->daoConfig->setField('isInvalid', 'select', '是否无效', array(0=>'有效', 1=>'无效'));
    }
}

class TestDao
{
    private $dao;

    public function __construct()
    {
//        parent::__construct();
        $this->dao = new Data();
    }

    public function test_getList()
    {
        $data = $this->dao->select('adminId, name, face, isInvalid')->where(array(
            'sex'=>1,
            'account like'=>'%anna%'
        ))->limit(12)->offset(5)->getList();
        var_dump($data);
    }

    public function test_count()
    {
        $data = $this->dao->select('adminId, name, face, isInvalid')->where(array(
            'sex'=>1,
            'account like'=>'%anna%'
        ))->limit(12)->offset(5)->count();
        var_dump($data);
    }

    public function test_add()
    {
        $this->dao->insert(array(
            'account'=>'wangwang',
            'name'=>'狗狗'
        ));
    }

    public function test_update()
    {
        $this->dao->updateById(50, array('face'=>'aaaa', 'name'=>'小狗'));
    }

    public function test_delete()
    {
        $this->dao->delete(52);
    }
}

$test = new TestDao();

$test->test_delete();