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

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use localzet\FrameX\View;
use function app_path;
use function array_merge;
use function base_path;
use function config;
use function is_array;
use function request;

/**
 * FrameX Twig: Templating adapter (twig/twig)
 */
class Twig implements View
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
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public static function render(string $template, array $vars, string $app = null): string
    {
        static $views = [];
        $request = request();
        $plugin = $request->plugin ?? '';
        $app = $app === null ? $request->app : $app;
        $configPrefix = $plugin ? "plugin.$plugin." : '';
        $viewSuffix = config("{$configPrefix}view.options.view_suffix", 'html');
        $key = "$plugin-$request->app";
        if (!isset($views[$key])) {
            $baseViewPath = $plugin ? base_path() . "/plugin/$plugin/app" : app_path();
            $viewPath = $app === '' ? "$baseViewPath/view/" : "$baseViewPath/$app/view/";
            $views[$key] = new Environment(new FilesystemLoader($viewPath), config("{$configPrefix}view.options", []));
            $extension = config("{$configPrefix}view.extension");
            if ($extension) {
                $extension($views[$key]);
            }
        }
        $vars = array_merge(static::$vars, $vars);
        $content = $views[$key]->render("$template.$viewSuffix", $vars);
        static::$vars = [];
        return $content;
    }
}
