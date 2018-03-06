<?php
class AdminGuide extends AdminBase
{
    public function show()
    {
        $this->assign('panelTitle', '新手向导');
        $stepPage = $this->getStepPage();
        $this->display('guide/'.$stepPage);
    }

    public function setDb()
    {
        try{
            $dbConfig = $this->request->getArray('config');
            $tables = AdminBuild::tablesFormatData($dbConfig);
            $this->buildConfig($dbConfig);
            $this->buildMenu($tables);
            $this->buildPages($tables);
            $this->response(true);
        } catch (PDOException $e) {
            $this->error('数据库连接失败');
        }
    }

    private function getStepPage()
    {
        if (config('config.DB.host' == '{{$host}}')) {
            return 'setDb.html';
        }
        return 'success.html';
    }

    private function buildConfig($dbConfig)
    {
        $buildPath = dirname(__DIR__) . '/build/config/config.php';
        $content = Template::compile($buildPath, $dbConfig);
        File::write(App::basePath() . '/config/config.php', $content, 'w');
        File::write(App::basePath() . '/config/config.local.php', $content, 'w');
    }

    private function buildMenu($tables)
    {
        $menu = array();
        $menuTpl = 'array(\'name\'=>\'%s\', \'key\'=>\'%s\'),';
        foreach ($tables as $table=>$params) {
            $menu[] = sprintf($menuTpl, $table, $params['path'] . '/pageHome');
        }
        $buildPath = dirname(__DIR__) . '/build/config/menu.php';
        $content = '<?php'."\r\n";
        $content .= Template::compile($buildPath, array('menu'=>implode("\r\n    ", $menu)));
        File::write(App::basePath() . '/config/menu.php', $content, 'w');
    }

    private function buildPages($tables)
    {
        foreach ($tables as $table=>$params) {
            $this->buildPage($params);
        }
    }

    private function buildPage($params)
    {
        $fields = array();
        $stringRow = '$this->daoConfig->setField(\'%s\', \'%s\', %s, \'请填写名称\');';
        $intRow = '$this->daoConfig->setField(\'%s\', \'%s\', \'请填写名称\');';
        foreach ($params['fields'] as $field=>$args) {
            if ($field == $params['primaryKey']) {
                continue;
            }
            if (empty($args['length'])) {
                $fields[] = sprintf($intRow, $field, $args['type']);
            } else {
                $fields[] = sprintf($stringRow, $field, $args['type'], $args['length']);
            }
        }
        $params['fields'] = implode("\r\n        ", $fields);
        $buildPath = dirname(__DIR__) . '/build/page/Table.php';
        $content = '<?php'."\r\n";
        $content .= Template::compile($buildPath, $params);
        File::write(App::basePath() . '/page/'.$params['path']. '.php', $content, 'w');
    }
}