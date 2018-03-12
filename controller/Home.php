<?php
namespace Controller;

class Home extends \Controller\AdminBase
{
    public function show()
    {
//        echo \AdminPaging::baseUrl();
//        echo $this->request->getInt('id', 12);
//        $this->post->getInt('id');
        var_dump($this->config('database'));
//        return array();
    }
}