<?php
namespace Model;

class Sample extends \Dao
{
    public function __construct()
    {
        $daoConfig = \Dao::newConfig();
        $daoConfig->setTable('z_onrec_doc_list');
        $daoConfig->setPrimaryKey('id');
        $daoConfig->setField('src_key', 'varchar', 50, '类型');
        $daoConfig->setField('doc_id', 'int', '文章id号');
        $daoConfig->setField('title', 'varchar',200, '标题');
        $daoConfig->setField('expire_time', 'datetime', '过期时间');
        $daoConfig->setField('input_time', 'datetime', '写入时间');
        $daoConfig->setField('hit', 'int', '点击量');
        $daoConfig->setField('rec', 'int', '推荐量');
        $daoConfig->setField('expired', 'tinyint', '是否过期');
        parent::__construct($daoConfig);
    }

}