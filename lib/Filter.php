<?php
class Filter
{
    public static function create($data)
    {
        return new self($data);
    }

    public static function isHtml($page)
    {
        return true;
    }

    public function openSafe()
    {

    }

    private $data = array();

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getInt($key, $default = null)
    {
        $result = isset($this->data[$key]) ? $this->data[$key] : $default;
        return empty($result) ? $default : (int)$result;
    }

    public function getArray($key, $default = null, $split = '_')
    {
        $result = isset($this->data[$key]) ? $this->data[$key] : $default;
        return is_string($result) ? explode($split, $result) : $result;
    }

    public function getString($key, $default = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    public function getText($key, $default = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    public function getHtml($key, $default = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    public function getEmail($key, $default = null)
    {
        $result = isset($this->data[$key]) ? $this->data[$key] : $default;
        return empty($result) ? $default : filter_var($result, FILTER_VALIDATE_EMAIL);
    }

    public function getBoolean($key, $default = null)
    {
        $result = isset($this->data[$key]) ? $this->data[$key] : $default;
        return empty($result) ? false : true;
    }
}