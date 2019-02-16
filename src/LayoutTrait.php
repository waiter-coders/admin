<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 17:28
 */

namespace Waiterphp\Admin;

trait LayoutTrait
{
    use BaseTrait;

    private $adminConfig;
    private $adminDao;

    public function __construct($adminConfig)
    {
        $this->config = $adminConfig;
        $this->adminDao = $adminConfig->getDao();
    }
}