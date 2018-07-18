<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/29
 * Time: 15:11
 */

namespace Model;

use Waiterphp\Core\DaoTrait;

class AdminGroup extends Base
{
    use DaoTrait;
    protected function setDaoConfig()
    {
        $this->daoConfig->setTable('classify_nodes');
        $this->daoConfig->setPrimaryKey('nodeId');
//        $this->daoConfig->setJoinTable('classify_topic', 'topicId');
        $this->daoConfig->setField('label', 'string', 30, '名称');
        $this->daoConfig->setField('parentId', 'number', '父节点id');
        $this->daoConfig->setFieldDefault('parentId', 0);
        $this->daoConfig->setField('preNodeId', 'number', '左节点');
        $this->daoConfig->setFieldDefault('preNodeId', 0);
        $this->daoConfig->setField('nextNodeId', 'number', '右节点');
        $this->daoConfig->setFieldDefault('nextNodeId', 0);
        $this->daoConfig->setField('layer', 'number', '层级');
        $this->daoConfig->setFieldDefault('layer', 0);
        $this->daoConfig->setField('trace', 'string', '节点轨迹');
        $this->daoConfig->setFieldDefault('trace', '[]');
        $this->daoConfig->setField('isLeaf', 'number', '是否叶子节点');
        $this->daoConfig->setFieldDefault('isLeaf', 0);
        $this->daoConfig->setDefaultQuery(array(
            'topicId'=>2
        ));

    }
}