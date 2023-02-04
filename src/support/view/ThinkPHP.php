<?php

/**
 * @package     Triangle Engine (FrameX)
 * @link        https://github.com/localzet/FrameX
 * @link        https://github.com/Triangle-org/Engine
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2022 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace support\view;

use think\Template;
use localzet\FrameX\View;
use function app_path;
use function array_merge;
use function base_path;
use function config;
use function is_array;
use function ob_get_clean;
use function ob_start;
use function request;
use function runtime_path;

/**
 * FrameX ThinkPHP: Templating adapter (topthink/think-template)
 */
class ThinkPHP implements View
{
    /**
     * @var array
     */
    protected static $vars = [];

    /**
     * @param string|array $name
     * @param mixed $value
     */
    public static function assign($name, $value = null)
    {
        static::$vars = array_merge(static::$vars, is_array($name) ? $name : [$name => $value]);
    }

    public static function vars()
    {
        return static::$vars;
    }

    /**
     * @param string $template
     * @param array $vars
     * @param string|null $app
     * @return false|string
     */
    public static function render(string $template, array $vars, string $app = null): string
    {
        $request = request();
        $plugin = $request->plugin ?? '';
        $app = $app === null ? $request->app : $app;
        $configPrefix = $plugin ? "plugin.$plugin." : '';
        $viewSuffix = config("{$configPrefix}view.options.view_suffix", 'html');
        $baseViewPath = $plugin ? base_path() . "/plugin/$plugin/app" : app_path();
        $viewPath = $app === '' ? "$baseViewPath/view/" : "$baseViewPath/$app/view/";
        $defaultOptions = [
            'view_path' => $viewPath,
            'cache_path' => runtime_path() . '/views/',
            'view_suffix' => $viewSuffix
        ];
        $options = $defaultOptions + config("{$configPrefix}view.options", []);
        $views = new Template($options);
        ob_start();
        $vars = array_merge(static::$vars, $vars);
        $views->fetch($template, $vars);
        $content = ob_get_clean();
        static::$vars = [];
        return $content;
    }
}
