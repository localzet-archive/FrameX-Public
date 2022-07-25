<?php

/**
 * @version     1.0.0-dev
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

use Jenssegers\Blade\Blade as BladeView;
use localzet\FrameX\View;

/**
 * Class Blade
 * composer require jenssegers/blade
 * @package support\view
 */
class Blade implements View
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
        static $views = [];
        $app = $app === null ? \request()->app : $app;
        if (!isset($views[$app])) {
            $view_path = $app === '' ? \app_path() . '/view' : \app_path() . "/$app/view";
            $views[$app] = new BladeView($view_path, \runtime_path() . '/views');
        }
        $vars = \array_merge(static::$_vars, $vars);
        $content = $views[$app]->render($template, $vars);
        static::$_vars = [];
        return $content;
    }
}
