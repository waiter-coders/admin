<?php
namespace Controller;


class Dashboard extends \Controller\Base
{
    public function getMenus()
    {
        return array(
            array("domain"=>"admin", "title"=>"系统管理", "children"=>array(
                array("domain"=>"account","title"=>"管理员信息", "children"=>array(
                    array("domain"=>"edit","title"=>"管理员信息编辑")
                )),
                array("domain"=>"invite","title"=>"邀请新管理员"),
                array("domain"=>"group","title"=>"管理员分组"),
            )),
            array("domain"=>"statistics", "title"=>"网站统计", "children"=>array(
                array("domain"=>"pvUv","title"=>"每日pv(uv)",),
                array("domain"=>"userRatio","title"=>"用户分布")
            )),
            array("domain"=>"doc", "title"=>"后台开发文档")
        );
    }
}