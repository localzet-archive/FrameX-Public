<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io/framex
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.ru>
 * @copyright   Copyright (c) 2018-2022 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace localzet\FrameX;

class Middleware
{
    /**
     * @var array
     */
    protected static $_instances = [];

    /**
     * @param array $all_middlewares
     * @param string $plugin
     * @return void
     */
    public static function load($all_middlewares, string $plugin = '')
    {
        if (!\is_array($all_middlewares)) {
            return;
        }

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
                    static::$_instances[$plugin][$app_name][] = [$class_name, 'process'];
                } else {
                    // @todo Log
                    echo "Промежуточный $class_name::process не существует\n";
                }
            }
        }
    }

    /**
     * @param string $plugin
     * @param string $app_name
     * @param bool $with_global_middleware
     * @return array|mixed
     */
    public static function getMiddleware(string $plugin, string $app_name, bool $with_global_middleware = true)
    {
        // Глобальная midleware
        $global_middleware = $with_global_middleware && isset(static::$_instances[$plugin]['']) ? static::$_instances[$plugin][''] : [];
        if ($app_name === '') {
            return \array_reverse($global_middleware);
        }
        // midleware приложения
        $app_middleware = static::$_instances[$plugin][$app_name] ?? [];
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
     * @deprecated
     * @return void
     */
    public static function container($_)
    {
    }
}
