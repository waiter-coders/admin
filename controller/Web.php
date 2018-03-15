<?php
namespace Controller;

class Web extends \Controller\Base
{
    public function menu()
    {
        return $this->config('menu');
    }
}