<?php


namespace Response;


class Error extends Base
{
    private $e;
    private $isDebug;

    public function __construct(\Exception $e)
    {
        $this->e = $e;
        $this->isDebug = scenes('main')->getConfig('isDebug');
    }

    public function output()
    {
        return $this->isDebug ? $this->debugOutput() : $this->workerOutput();
    }

    private function workerOutput()
    {
        return json_encode(array('code'=>$this->e->getCode(), 'msg'=>$this->e->getMessage()));
    }

    private function debugOutput()
    {
        throw new \Exception($this->e->getMessage(), $this->e->getCode());
    }

}