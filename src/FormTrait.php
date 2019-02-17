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

    public function getFormData($request)
    {
        $primaryKey = $this->dao->primaryKey();
        $id = $request->getInt($primaryKey);
        return $this->dao->infoById($id);
    }

    public function formSubmit($request)
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

    public function formUpload($request)
    {
        $field = $request->getString('field');
        $fieldInfo = $this->config->getField($field);
        assert_exception($fieldInfo['type'] == 'image', 'only type image can update' . json_encode($fieldInfo));
        assert_exception(isset($fieldInfo['basePath']) && is_dir($fieldInfo['basePath']), 'base path error');
        assert_exception(isset($fieldInfo['width']) && isset($fieldInfo['height']), 'width height set error');
        $upload = \Tools\Upload::get($field);
        $image = \Tools\Image::get($upload->file);
        $datePath = $this->generateDatePath($upload->name);
        $image->scale($fieldInfo['width'], $fieldInfo['height'])->save($fieldInfo['basePath'] . '/'. $datePath, true);
        return $datePath;
    }

    public function editorUpload($request)
    {
        $field = $request->getString('field');
        $fieldInfo = $this->config->getField($field);
        assert_exception($fieldInfo['type'] == 'editor', 'not editor' . json_encode($fieldInfo));
        assert_exception(isset($fieldInfo['basePath']) && is_dir($fieldInfo['basePath']), 'base path error');
        $upload = \Tools\Upload::get($field);
        $image = \Tools\Image::get($upload->file);
        $datePath = $this->generateDatePath($upload->name);
        $image->save($fieldInfo['basePath'] .  '/'. $datePath, true);
        return $datePath;
    }

    private function generateDatePath($filename)
    {
        $extend = pathinfo($filename, PATHINFO_EXTENSION);
        return date('Y-m-d') . '/' . time() . '.' . $extend;
    }
}