<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/13
 * Time: 18:00
 */

namespace Response;


use Waiterphp\Core\OB;

class Json extends Base
{
    private $data;
    private $isDebug;

    public function __construct($data)
    {
        $this->data = $data;
        $this->isDebug = scenes('main')->getConfig('isDebug');
    }

    public function output()
    {
        return $this->isDebug ? $this->defaultOutput() : $this->workerOutput();
    }

    private function workerOutput()
    {
        OB::endClean();
        return $this->defaultOutput();
    }

    private function defaultOutput()
    {
        return json_encode(array('code'=>0, 'data'=>$this->data));
    }
}