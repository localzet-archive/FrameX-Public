<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io
 * 
 * @author      localzet <creator@localzet.ru>
 * 
 * @copyright   Copyright (c) 2018-2020 Zorin Projects 
 * @copyright   Copyright (c) 2020-2022 NONA Team
 * 
 * @license     https://www.localzet.ru/license GNU GPLv3 License
 */

namespace support\view;

use localzet\FrameX\View;

/**
 * Class Raw
 */
class Raw implements View
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
     * @return false|string
     */
    public static function render(string $template, array $vars, string $app = null)
    {
        $request = \request();
        $plugin = $request->plugin ?? '';
        $config_prefix = $plugin ? "plugin.$plugin." : '';
        $view_suffix = \config("{$config_prefix}view.options.view_suffix", 'html');
        $app = $app === null ? $request->app : $app;
        $base_view_path = $plugin ? \base_path() . "/plugin/$plugin/app" : \app_path();
        $__template_path__ = $app === '' ? "$base_view_path/view/$template.$view_suffix" : "$base_view_path/$app/view/$template.$view_suffix";

        \extract(static::$_vars);
        \extract($vars);
        \ob_start();
        // Try to include php file.
        try {
            include $__template_path__;
        } catch (\Throwable $e) {
            static::$_vars = [];
            \ob_end_clean();
            throw $e;
        }
        static::$_vars = [];
        return \ob_get_clean();
    }
}
