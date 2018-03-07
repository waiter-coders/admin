<?php
namespace Controller;

class Home extends \Controller\Base
{
    public function show()
    {
        echo \AdminPaging::baseUrl();
        echo $this->request()->getInt('id', 12);
//        return $this->fetchView('tmp', array());
    }
}