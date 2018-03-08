<?php
namespace Controller;

class Home extends \Controller\Base
{
    public function show()
    {
        echo \AdminPaging::baseUrl();
        echo $this->request()->getInt('id', 12);
        var_dump($this->config('database.default.aa'));
    }
}