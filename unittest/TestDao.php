<?php
namespace UnitTest;

class TestDao extends TestBase
{
    private $userDao = '';

    public function SetUp()
    {
        parent::SetUp();
        $this->userDao = \Dao::load('user', function (\DaoConfig $daoConfig) {
            $daoConfig->setPrimaryKey('id');
            $daoConfig->setField('type', 'enum', 3, '类型', $this->typeMap());
            $daoConfig->setField('name', 'varchar', 255, '名称');
            $daoConfig->setField('info', 'varchar', 255, '简介');
            $daoConfig->setField('image', 'varchar', 255, '图片');
            $daoConfig->setField('content', 'html', '内容');
            $daoConfig->setField('url', 'varchar', 255, '连接');
            $daoConfig->setField('upload', 'varchar', 255, '上传文件');
            $daoConfig->setField('addTime', 'datetime', '添加时间');
            $daoConfig->setField('updateTime', 'timestamp', '更新时间');
            $daoConfig->setFieldDefault('addTime', date('Y-m-d H:i:s'));
            $daoConfig->setFieldsReadonly('addTime', 'updateTime');
        });
    }

    private function typeMap()
    {
        return array(
            1 => '测试',
            2 => '测试2'
        );
    }

    public function test_infoById()
    {
        $info = $this->userDao->infoById(1);
        var_dump($info);
    }
}




