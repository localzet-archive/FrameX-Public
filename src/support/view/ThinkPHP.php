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

use think\Template;
use localzet\FrameX\View;

/**
 * Class Blade
 */
class ThinkPHP implements View
{
    /**
     * @var array
     */
    protected static $_vars = [];

    /**
     * @param string|array $name
     * @param mixed $value
     */
    public static function assign(string $name, $value = null)
    {
        static::$_vars = \array_merge(static::$_vars, \is_array($name) ? $name : [$name => $value]);
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
        $app = $app === null ? $request->app : $app;
        $config_prefix = $plugin ? "plugin.$plugin." : '';
        $view_suffix = \config("{$config_prefix}view.options.view_suffix", 'html');
        $base_view_path = $plugin ? \base_path() . "/plugin/$plugin/app" : \app_path();
        $view_path = $app === '' ? "$base_view_path/view/" : "$base_view_path/$app/view/";
        $default_options = [
            'view_path' => $view_path,
            'cache_path' => \runtime_path() . '/views/',
            'view_suffix' => $view_suffix
        ];
        $options = $default_options + \config("{$config_prefix}view.options", []);
        $views = new Template($options);
        \ob_start();
        $vars = \array_merge(static::$_vars, $vars);
        $views->fetch($template, $vars);
        $content = \ob_get_clean();
        static::$_vars = [];
        return $content;
    }
}
