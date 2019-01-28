<?php
namespace Model;

use Waiterphp\Core\DaoTrait;

class Admin extends Base
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
//        $this->daoConfig->setField('platform', 'multiSelect', '所属平台', $this->config('platform'));
//        $this->daoConfig->setField('city', 'linkSelect');
//        $this->daoConfig->setField('country', 'tinyint', '国家');
//        $this->daoConfig->setField('province', 'tinyint', '省');
//        $this->daoConfig->setField('city', 'tinyint', '市');
//        $this->daoConfig->setField('face', 'image', 50, '头像');
//        $this->daoConfig->setField('mobile', 'phone', 11, '手机号');
//        $this->daoConfig->setField('email', 'email', 50, '邮箱');
        $this->daoConfig->setField('addTime', 'datetime', '添加时间');
        $this->daoConfig->setField('isInvalid', 'select', '是否无效', array(0=>'有效', 1=>'无效'));
    }
}