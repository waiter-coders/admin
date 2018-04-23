<?php
namespace Model;

class ___Dao___ extends \Dao
{
    public function __construct()
    {
        $daoConfig = \Dao::newConfig();
        // 数据源配置
        $daoConfig->setTable('paging_simple');
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
        parent::__construct($daoConfig);
    }

    private function typeMap()
    {
        return array(
            1=>array('name'=>'测试'),
            2=>array('name'=>'测试2'),
        );
    }
}