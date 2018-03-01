<?php
class Validate
{
    private $selectType = 'get';

    public function setType($type)
    {
        $this->selectType = strtolower($type);
    }

    public function getInt($key, $default = null)
    {
        $result = $this->get($key, $default);
        return empty($result) ? $default : (int)$result;
    }

    public function getArray($key, $default = null)
    {
        return $this->get($key, $default);
    }

    public function getString($key, $default = null)
    {
        return $this->get($key, $default);
    }

    public function getText($key, $default = null)
    {
        return $this->get($key, $default);
    }

    public function getHtml($key, $default = null)
    {
        return $this->get($key, $default);
    }

    public function getEmail($key, $default = null)
    {
        $result = $this->get($key, $default);
        return empty($result) ? $default : filter_var($this->get($key, $default), FILTER_VALIDATE_EMAIL);
    }

    public function getBoolean($key, $default = null)
    {
        return empty($this->get($key, $default)) ? false : true;
    }

    private function get($key, $default = null)
    {
        if ($this->selectType == 'cli') {
            return getopt($key.':');
        }
        switch($this->selectType) {
            case 'get':
                $request = $_GET;
                break;
            case 'post':
                $request = $_POST;
                break;
            default:
                $request = $_GET + $_POST;
        }
        $return = isset($request[$key]) ? urldecode($request[$key]) : $default;
        if ($return === null) {
            throw new Exception('must set request:'.$key);
        }
        return $return;
    }

    public function isAjax()
    {
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return false;
        }
        $ajaxTab = strtolower($_SERVER['HTTP_X_REQUESTED_WITH']);
        return ($ajaxTab == 'xmlhttprequest') ? true : false;
    }

    public function openSafe()
    {

    }
}
