<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 17:28
 */

namespace Waiterphp\Admin;


trait FormTrait
{
    use BaseTrait;

    public function fetchData($request)
    {
        $primaryKey = $this->dao->primaryKey();
        $id = $request->getInt($primaryKey);
        return $this->dao->infoById($id);
    }

    public function submit($request)
    {
        $primaryKey = $this->dao->primaryKey();
        $id = $request->getInt($primaryKey, 0);
        $formData = $request->getArray('formData');
        // 新加
        if (empty($id)) {
            $defaultData = $this->config->getFieldsDefault();
            $formData = array_merge($defaultData, $formData);
            $id = $this->dao->insert($formData);
        }
        // 编辑
        else {
            $this->dao->updateById($id, $formData);
        }
        return $id;
    }

    public function upload($request)
    {
        $field = $request->getString('field');
        $fieldInfo = $this->config->getField($field);
        assert_exception(isset($fieldInfo['basePath']) && is_dir($fieldInfo['basePath']), 'base path error');
        if ($fieldInfo['type'] == 'image') {
        assert_exception(isset($fieldInfo['width']) && isset($fieldInfo['height']), 'width height set error');
            return $this->uploadImage($field, $fieldInfo['basePath'], $fieldInfo['width'], $fieldInfo['height']);
        }
        if ($fieldInfo['type'] == 'editor') {
            return $this->uploadImage($field, $fieldInfo['basePath']);
        }
        throw new \Exception('not support upload type:' . $fieldInfo['field']);
        
    }

    private function uploadImage($field, $basePath, $width = 0, $height = 0, $pathType = 'date')
    {        
        $upload = \Waiterphp\Core\Upload\Upload::get($field);
        $image = \Waiterphp\Core\Image\Image::get($upload->file);
        $filePath = $this->generatePath($pathType, $upload->name);
        if ($width != 0 && $height != 0) {
            $image = $image->scale($width, $height);
        }
        $image->save($basePath . '/'. $filePath, true);
        return $filePath;
    }

    private function generatePath($pathType, $filename)
    {
        $extend = pathinfo($filename, PATHINFO_EXTENSION);
        if ($pathType == 'date') {
            return date('Y-m-d') . '/' . time() . '.' . $extend;
        }
        return $filename;
    }
}