<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 18:49
 */

namespace Controller;


class User extends AdminBase
{
    public function canUseDomains()
    {
        $domains = parent::canUseDomains();
        return $this->domainsToTree($domains);
    }

    private function domainsToTree($domains, $needFather = '')
    {
        $response = array();
        foreach ($domains as $domain) {
            $father = implode('.', explode('.', $domain['domain'], -1));
            if ($father == $needFather) {
                $children = $this->domainsToTree($domains, $domain['domain']);
                if (!empty($children)) {
                    $domain['children'] = $children;
                }
                $response[] = $domain;
            }
        }
        return $response;
    }

}