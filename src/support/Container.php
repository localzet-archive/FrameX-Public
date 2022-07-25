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

namespace support;

use Psr\Container\ContainerInterface;

/**
 * Class Container
 * @package support
 * @method static mixed get($name)
 * @method static mixed make($name, array $parameters)
 * @method static bool has($name)
 */
class Container
{
    /**
     * @var ContainerInterface
     */
    protected static $_instance = null;

    /**
     * @return ContainerInterface
     */
    public static function instance()
    {
        if (!static::$_instance) {
            static::$_instance = include config_path() . '/container.php';
        }
        return static::$_instance;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::instance()->{$name}(...$arguments);
    }
}
