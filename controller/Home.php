<?php
namespace Controller;

class Home extends \Controller\AdminBase
{
    public function show()
    {

    }

    public function getConfig()
    {
        return $this->model('record')->infoById(1);
    }
}