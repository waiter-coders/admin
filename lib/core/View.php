<?php

/*
 * *【网页】***************************
 * 提供模板渲染机制和page分离化基类*
 *************************************
 *
 * 使用方法：
 *
 * 注意：
 * ----使用twig模板引擎
 *
 */

class View
{
    private static $config = null;
    private static $params = array();

    public static function config(array $configs)
    {
        self::$config = $configs;
    }

    public static function getConfig()
    {
        return self::$config;
    }

    public static function assign($key, $value)
    {
        self::$params[$key] = $value;
    }

    public static function assignArray(array $params)
    {
        self::$params = array_merge(self::$params, $params);
    }

    public static function assignCovertJson($key, $value = null)
    {
        if ($value === null && isset(self::$params[$key])) {
            $value = self::$params[$key];
        }
        $value = str_replace('"', '\"', $value);
        $value = json_encode($value);
        self::$params[$key . '_json'] = $value;
    }

    public static function display($template)
    {
        $view = self::instance();
        $params = (!empty(self::$config['init'])) ? self::$config['init'] : array();
        $params = array_merge($params, self::$params);
        $view->render($template, $params);
    }

    public static function fetch($template, $params)
    {
        $view = self::instance();
        $initParams = (!empty(self::$config['init'])) ? self::$config['init'] : array();
        $params = array_merge($initParams, $params);
        $params = array_merge($params, self::$params);
        return $view->fetch($template, $params);
    }

    private static function instance()
    {
        static $view = null;
        if (empty($view)) {
            if (empty(self::$config['template']) || empty(self::$config['compile'])) {
                throw new Exception('template path not set');
            }
            $engine = isset(self::$config['type']) ? ucfirst(self::$config['type']) : 'Smarty';
            $class = '\\Vendor\\View\\' . $engine . 'Render';
            $view = new $class(self::$config);
        }
        return $view;
    }

    public static function redirect($jumpUrl, $message = '', $time = 5)
    {
        // 没有包含域名的自动包含域名
        if (strncmp ($jumpUrl, 'http', 4)) {
            $jumpUrl = \Url::baseUrl() . '/' . ltrim($jumpUrl, '/');
        }

        // 直接跳转
        if (empty($message)) {
            ob_end_clean();
            header("Location:" . $jumpUrl);
        }

        // 页面提示，并若干秒后跳转
        else if (isset(self::$config['redirectPage'])) {
            self::assign('message', $message);
            self::assign('jumpUrl', $jumpUrl);
            self::assign('time', $time);
            self::display(self::$config['redirectPage']);
        }

        // js跳转
        else {
            ob_end_clean();
            echo '<script>';
            echo 'alert(' . $message . ');';
            echo 'document.location = "' . $jumpUrl . '";';
            echo '</script>';
        }
        exit;
    }

    public static function postRedirect($url, $post)
    {
        $form = '';
        foreach ($post as $key=>$value) {
            if (strpos($value, "\n") || strlen($value) > 50) {
                $form .= '<textarea name="'.$key.'" style="display:none;">'.$value.'</textarea>';
            } else {
                $form .= '<input type="hidden" value="'.$value.'" name="'.$key.'">';
            }
        }
        echo <<<EOT
<form action="{$url}" method="POST">
{$form}
</form>
<script type="text/javascript">
document.forms[0].submit();
</script>
EOT;
        exit;
    }

    public static function error()
    {
        if (isset(self::$config['errorPage'])) {

        } else {
            echo 'error 404';
        }
        exit;
    }
}

abstract class RenderBase
{
    abstract public function render();
}



