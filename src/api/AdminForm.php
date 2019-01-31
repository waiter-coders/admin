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
        $id = $request->getInt($this->adminDao->primaryKey());
        return $this->adminDao->infoById($id);
    }

    public function formSubmit($request)
    {
        $id = $request->getInt($this->adminDao->primaryKey(), 0);
        $formData = $request->getArray('formData');
        // 新加
        if (empty($id)) {
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
        $upload = \Lib\Upload::get($field);
        $image = \Lib\Image::get($upload->file);
        $basePath = IMAGE_PATH . '/product';
        $goodsPath = $this->getGoodsPath($upload->name);
        $image->scale(240, 180)->save($basePath . '/'. $goodsPath, true);
        return $goodsPath;
    }

    public function formCheck()
    {

    }

    public function editorUpload($request)
    {
        $field = $request->getString('field');
        $upload = \Lib\Upload::get($field);
        $image = \Lib\Image::get($upload->file);
        $basePath = IMAGE_PATH . '/product';
        $goodsPath = $this->getGoodsPath($upload->name);
        $image->save($basePath . '/'. $goodsPath, true);
        return 'http://image.teamcorp.cn/wo_de/product/'. $goodsPath;
    }

    private function getGoodsPath($filename)
    {
        $extend = pathinfo($filename, PATHINFO_EXTENSION);
        return date('Y-m-d') . '/' . time() . '.' . $extend;
    }
}