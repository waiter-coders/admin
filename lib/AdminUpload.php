<?php
class AdminUpload
{
    private $field = '';
    private $uploadFilter = '';
    private $showFilter = '';
    private $baseUrl = '';

    public function __construct($field)
    {
        $this->$field = $field;
    }

    public function setUploadFilter(callable $filter)
    {
        $this->uploadFilter = $filter;
        return $this;
    }

    public function setShowFilter(callable $filter)
    {
        $this->showFilter = $filter;
        return $this;
    }

    public function setBaseUrl($url)
    {
        $this->baseUrl = $url;
        return $this;
    }

    public function upload()
    {
        $upload = \Upload::get($this->field);
        $uploadFilter = empty($this->uploadFilter) ? array($this, 'defaultUploadFilter') : $this->uploadFilter;
        $saveValue = call_user_func($uploadFilter, array($upload));
        return $saveValue;
    }

    public function show()
    {
        $upload = \Upload::get($this->field);
        $uploadFilter = empty($this->uploadFilter) ? array($this, 'defaultUploadFilter') : $this->uploadFilter;
        $saveValue = call_user_func($uploadFilter, array($upload));
        return $saveValue;
    }
}