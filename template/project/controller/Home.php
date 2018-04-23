<?php
namespace Controller;

class Home extends \Controller\ToolsBase
{
    public function show()
    {
        $this->render('home.html', array(
            'name'=>'hello world',
        ));
    }

    public function getConfig()
    {
        return $this->model('record')->infoById(1);
    }
}