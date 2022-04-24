<?php
/**
 * @author    localzet<creator@localzet.ru>
 * @copyright localzet<creator@localzet.ru>
 * @link      https://www.localzet.ru/
 * @license   https://www.localzet.ru/license GNU GPLv3 License
 */

namespace support\view;

use think\Template;
use localzet\FrameX\View;

/**
 * Class Blade
 * @package support\view
 */
class ThinkPHP implements View
{
    /**
     * @var array
     */
    protected static $_vars = [];

    /**
     * @param $name
     * @param null $value
     */
    public static function assign($name, $value = null)
    {
        static::$_vars = \array_merge(static::$_vars, \is_array($name) ? $name : [$name => $value]);
    }

    /**
     * @param $template
     * @param $vars
     * @param string $app
     * @return mixed
     */
    public static function render($template, $vars, $app = null)
    {
        $app = $app === null ? \request()->app : $app;
        $view_path = $app === '' ? \app_path() . '/view/' : \app_path() . "/$app/view/";
        $default_options = [
            'view_path' => $view_path,
            'cache_path' => \runtime_path() . '/views/',
            'view_suffix' => config('view.view_suffix', 'html')
        ];
        $options = $default_options + \config('view.options', []);
        $views = new Template($options);
        \ob_start();
        $vars = \array_merge(static::$_vars, $vars);
        $views->fetch($template, $vars);
        $content = \ob_get_clean();
        static::$_vars = [];
        return $content;
    }
}
