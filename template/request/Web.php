<?php

namespace Request;


class Web extends Base
{
    public function __construct()
    {
        $data = $_GET + $_POST;
        $this->setFilterData($data);
//        $_GET = $_POST = [];
    }
}