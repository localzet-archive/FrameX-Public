<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io/framex
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.ru>
 * @copyright   Copyright (c) 2018-2022 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace support\view;

use Jenssegers\Blade\Blade as BladeView;
use localzet\FrameX\View;

/**
 * rameX Blade: Templating adapter (jenssegers/blade)
 */
class Blade implements View
{
    /**
     * @var array
     */
    protected static $_vars = [];

    /**
     * @param string|array $name
     * @param mixed $value
     */
    public static function assign($name, $value = null)
    {
        static::$_vars = \array_merge(static::$_vars, \is_array($name) ? $name : [$name => $value]);
    }

    public static function vars() {
        return static::$_vars;
    }

    /**
     * @param string $template
     * @param array $vars
     * @param string|null $app
     * @return string
     */
    public static function render(string $template, array $vars, string $app = null)
    {
        static $views = [];
        $request = \request();
        $plugin = $request->plugin ?? '';
        $app = $app === null ? $request->app : $app;
        $base_view_path = $plugin ? \base_path() . "/plugin/$plugin/app" : \app_path();
        if (!isset($views[$app])) {
            $view_path = $app === '' ? "$base_view_path/view" : "$base_view_path/$app/view";
            $views[$app] = new BladeView($view_path, \runtime_path() . '/views');
        }
        $vars = \array_merge(static::$_vars, $vars);
        $content = $views[$app]->render($template, $vars);
        static::$_vars = [];
        return $content;
    }
}
