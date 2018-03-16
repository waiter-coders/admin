<?php
class Request
{
    public static function get()
    {
        return new RequestInstance($_GET);
    }

    public static function post()
    {
        return new RequestInstance($_POST);
    }
}

class RequestInstance
{
    private $input = array();

    public function __construct($input)
    {
        $this->input = $input;
    }

    public function getInt($key, $default = null)
    {
        $result = isset($this->input[$key]) ? $this->input[$key] : $default;
        return empty($result) ? $default : (int)$result;
    }

    public function getArray($key, $default = null)
    {
        return isset($this->input[$key]) ? $this->input[$key] : $default;
    }

    public function getString($key, $default = null)
    {
        return isset($this->input[$key]) ? $this->input[$key] : $default;
    }

    public function getText($key, $default = null)
    {
        return isset($this->input[$key]) ? $this->input[$key] : $default;
    }

    public function getHtml($key, $default = null)
    {
        return isset($this->input[$key]) ? $this->input[$key] : $default;
    }

    public function getEmail($key, $default = null)
    {
        $result = isset($this->input[$key]) ? $this->input[$key] : $default;
        return empty($result) ? $default : filter_var($result, FILTER_VALIDATE_EMAIL);
    }

    public function getBoolean($key, $default = null)
    {
        $result = isset($this->input[$key]) ? $this->input[$key] : $default;
        return empty($result) ? false : true;
    }
}