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

namespace support;

use Psr\Container\ContainerInterface;

use localzet\FrameX\App;
use localzet\FrameX\Config;

/**
 * Class Container
 * @method static mixed get($name)
 * @method static mixed make($name, array $parameters)
 * @method static bool has($name)
 */
class Container
{
    /**
     * @return ContainerInterface
     */
    public static function instance(string $plugin = '')
    {
        return Config::get($plugin ? "plugin.$plugin.container" : 'container');
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $plugin = App::getPluginByClass($name);
        return static::instance($plugin)->{$name}(...$arguments);
    }
}
