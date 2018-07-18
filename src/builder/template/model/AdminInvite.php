<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/29
 * Time: 15:11
 */

namespace Model;

use Waiterphp\Core\DaoTrait;

class AdminInvite extends Base
{
    use DaoTrait;
    protected function setDaoConfig()
    {
        $this->daoConfig->setTable('admin_invite');
        $this->daoConfig->setPrimaryKey('inviteId');
        $this->daoConfig->setField('email', 'email', 50, '邮箱');
        $this->daoConfig->setField('content', 'html', 50, '邮箱内容');
        $this->daoConfig->setField('isInvalid', 'number', '是否无效', array(0=>'有效', 1=>'无效'));
        $this->daoConfig->setField('addTime', 'datetime', '添加时间');
    }
}