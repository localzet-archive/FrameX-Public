<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io/framex
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.ru>
 * @copyright   Copyright (c) 2018-2022 Localzet Group
 * @license     https://www.localzet.ru/license GNU GPLv3 License
 */

namespace support\view;

use localzet\FrameX\View;

/**
 * @package FrameX Raw: PHP Templating engine
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

    public static function vars()
    {
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
        $view_head = \config("{$config_prefix}view.options.view_head", "base");
        $view_footer = \config("{$config_prefix}view.options.view_footer", "footer");
        $app = $app === null ? $request->app : $app;
        $base_view_path = $plugin ? \base_path() . "/plugin/$plugin/app" : \app_path();
        $__template_head__ = $app === '' ? "$base_view_path/view/$view_head.$view_suffix" : "$base_view_path/$app/view/$view_head.$view_suffix";
        $__template_body__ = $app === '' ? "$base_view_path/view/$template.$view_suffix" : "$base_view_path/$app/view/$template.$view_suffix";
        $__template_foot__ = $app === '' ? "$base_view_path/view/$view_footer.$view_suffix" : "$base_view_path/$app/view/$view_footer.$view_suffix";

        \extract(static::$_vars);
        \extract($vars);
        \ob_start();

        try {
            if (file_exists($__template_head__)) include $__template_head__;
            include $__template_body__;
            if (file_exists($__template_foot__)) include $__template_foot__;
        } catch (\Throwable $e) {
            static::$_vars = [];
            \ob_end_clean();
            throw $e;
        }

        static::$_vars = [];
        return \ob_get_clean();
    }
}
