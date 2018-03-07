<?php
namespace Controller;

class Home extends \Controller\Base
{
    public function show()
    {
        echo \AdminPaging::baseUrl();
    }
}