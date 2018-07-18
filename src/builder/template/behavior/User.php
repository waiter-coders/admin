<?php
namespace Behaviors;

class User extends Base
{
    public function listen()
    {
        $this->register('response', 'response');
        $this->register('response', 'response');
    }

    private function response()
    {

    }
}