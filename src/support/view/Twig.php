<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io/framex
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.ru>
 * @copyright   Copyright (c) 2018-2022 RootX Group
 * @license     https://www.localzet.ru/license GNU GPLv3 License
 */

namespace support\view;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use localzet\FrameX\View;

/**
 * @package FrameX Twig: Templating adapter (twig/twig)
 */
class Twig implements View
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
        $config_prefix = $plugin ? "plugin.$plugin." : '';
        $view_suffix = \config("{$config_prefix}view.options.view_suffix", 'html');
        $key = "{$plugin}-{$request->app}";
        if (!isset($views[$key])) {
            $base_view_path = $plugin ? \base_path() . "/plugin/$plugin/app" : \app_path();
            $view_path = $app === '' ? "$base_view_path/view/" : "$base_view_path/$app/view/";
            $views[$key] = new Environment(new FilesystemLoader($view_path), \config("{$config_prefix}view.options", []));
        }
        $vars = \array_merge(static::$_vars, $vars);
        $content = $views[$key]->render("$template.$view_suffix", $vars);
        static::$_vars = [];
        return $content;
    }
}
