<?php
namespace Controller;

class Web extends \Controller\Base
{
    public function menu()
    {
        $this->model('sample')->infoById(1);
        return $this->config('menu');
    }
}