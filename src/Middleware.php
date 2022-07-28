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

namespace localzet\FrameX;

use Psr\Container\ContainerInterface;
use localzet\FrameX\App;



class Middleware
{
    /**
     * @var ContainerInterface
     */
    protected static $_container = null;

    /**
     * @var array
     */
    protected static $_instances = [];

    /**
     * @param $all_middlewares
     */
    public static function load($all_middlewares)
    {
        // $all_middlewares = [
        //     'app1' => [
        //         'Class1',
        //         'Class2',
        //         'Class3'
        //     ],
        //     'app2' => [
        //         'Class1',
        //         'Class2',
        //         'Class3'
        //     ]
        // ];

        foreach ($all_middlewares as $app_name => $middlewares) {
            if (!\is_array($middlewares)) {
                throw new \RuntimeException('Некорректная конфигурация промежуточного ПО');
            }
            foreach ($middlewares as $class_name) {
                if (\method_exists($class_name, 'process')) {
                    static::$_instances[$app_name][] = [static::container()->get($class_name), 'process'];
                } else {
                    // @todo Log
                    echo "Промежуточный $class_name::process не существует\n";
                }
            }
        }
    }

    /**
     * @param $app_name
     * @param bool $with_global_middleware
     * @return array
     */
    public static function getMiddleware($app_name, $with_global_middleware = true)
    {
        // Глобальная midleware
        $global_middleware = $with_global_middleware && isset(static::$_instances['']) ? static::$_instances[''] : [];
        if ($app_name === '') {
            return \array_reverse($global_middleware);
        }
        // midleware приложения
        $app_middleware = static::$_instances[$app_name] ?? [];
        return \array_reverse(\array_merge($global_middleware, $app_middleware));
    }

    /**
     * @param $app_name
     * @return bool
     */
    public static function hasMiddleware($app_name)
    {
        return isset(static::$_instances[$app_name]);
    }

    /**
     * @param $container
     * @return ContainerInterface
     */
    public static function container($container = null)
    {
        if ($container) {
            static::$_container = $container;
        }
        if (!static::$_container) {
            static::$_container = App::container();
        }
        return static::$_container;
    }
}
