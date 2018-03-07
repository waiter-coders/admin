<?php
namespace Controller\Helper;

class Helper extends \Controller\Base
{
    public function show()
    {
        var_dump($this->config('environment'));
        request()->getInt();
    }
}