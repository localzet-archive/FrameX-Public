<?php
/**
 * @author    localzet<creator@localzet.ru>
 * @copyright localzet<creator@localzet.ru>
 * @link      https://www.localzet.ru/
 * @license   https://www.localzet.ru/license GNU GPLv3 License
 */

namespace support\view;

use localzet\FrameX\View;

/**
 * Class Raw
 * @package support\view
 */
class Raw implements View
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
     * @param null $app
     * @return string
     */
    public static function render($template, $vars, $app = null)
    {
        static $view_suffix;
        $view_suffix = $view_suffix ?: \config('view.view_suffix', 'html');
        $app = $app === null ? \request()->app : $app;
        if ($app === '') {
            $view_path = \app_path() . "/view/$template.$view_suffix";
        } else {
            $view_path = \app_path() . "/$app/view/$template.$view_suffix";
        }
        \extract(static::$_vars);
        \extract($vars);
        \ob_start();
        // Try to include php file.
        try {
            include $view_path;
        } catch (\Throwable $e) {
            echo $e;
        }
        static::$_vars = [];
        return \ob_get_clean();
    }

}
