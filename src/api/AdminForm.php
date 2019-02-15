<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 17:28
 */

namespace Waiterphp\Admin\Api;


class AdminForm
{
    private $adminConfig;
    private $adminDao;

    public function __construct($adminConfig)
    {
        $this->adminConfig = $adminConfig;
        $this->adminDao = $adminConfig->getDao();
    }

    public function getFormData($request)
    {
        $primaryKey = $this->adminDao->primaryKey();
        $id = $request->getInt($primaryKey);
        return $this->adminDao->infoById($id);
    }

    public function formSubmit($request)
    {
        $primaryKey = $this->adminDao->primaryKey();
        $id = $request->getInt($primaryKey, 0);
        $formData = $request->getArray('formData');
        // 新加
        if (empty($id)) {
            $defaultData = $this->adminConfig->getFieldsDefault();
            $formData = array_merge($defaultData, $formData);
            $id = $this->adminDao->insert($formData);
        }
        // 编辑
        else {
            $this->adminDao->updateById($id, $formData);
        }
        return $id;
    }

    public function formUpload($request)
    {
        $field = $request->getString('field');
        $fieldInfo = $this->adminConfig->getField($field);
        assertOrException($fieldInfo['type'] == 'image', 'only type image can update' . json_encode($fieldInfo));
        assertOrException(isset($fieldInfo['basePath']) && is_dir($fieldInfo['basePath']), 'base path error');
        assertOrException(isset($fieldInfo['width']) && isset($fieldInfo['height']), 'width height set error');
        $upload = \Tools\Upload::get($field);
        $image = \Tools\Image::get($upload->file);
        $datePath = $this->generateDatePath($upload->name);
        $image->scale($fieldInfo['width'], $fieldInfo['height'])->save($fieldInfo['basePath'] . '/'. $datePath, true);
        return $datePath;
    }

    public function editorUpload($request)
    {
        $field = $request->getString('field');
        $fieldInfo = $this->adminConfig->getField($field);
        assertOrException($fieldInfo['type'] == 'editor', 'not editor' . json_encode($fieldInfo));
        assertOrException(isset($fieldInfo['basePath']) && is_dir($fieldInfo['basePath']), 'base path error');
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