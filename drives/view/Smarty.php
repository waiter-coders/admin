<?php
namespace Vendor\View;

class Smarty
{
    private $view;

    public function __construct($config)
    {
        $this->view = new \Smarty();
        $this->view->left_delimiter = isset($config['left']) ? $config['left'] : "{{";
        $this->view->right_delimiter = isset($config['right']) ? $config['right'] : "}}";
        $this->view->compile_check = true;
        $this->view->caching = false;
        $this->view->setTemplateDir($config['template']);
        $this->view->setCompileDir($config['compile']);
    }

    public function render($template, array $params)
    {
        foreach ($params as $key=>$value) {
            $this->view->assign($key, $value);
        }
        return $this->view->fetch($template);
    }
}