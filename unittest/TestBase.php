<?php
namespace UnitTest;
require '../start.php';

class TestBase extends \PHPUnit\Framework\TestCase
{
    public function SetUp()
    {
        \DB::register(array(
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
            'database' => 'sample',
            'prefix' => '',
        ));
    }

}