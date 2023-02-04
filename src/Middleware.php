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

namespace localzet\FrameX;

use RuntimeException;
use function array_merge;
use function array_reverse;
use function is_array;
use function method_exists;

class Middleware
{
    /**
     * @var array
     */
    protected static $instances = [];

    /**
     * @param array $allMiddlewares
     * @param string $plugin
     * @return void
     */
    public static function load($allMiddlewares, string $plugin = '')
    {
        if (!is_array($allMiddlewares)) {
            return;
        }

        // $allMiddlewares = [
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

        foreach ($allMiddlewares as $appName => $middlewares) {
            if (!is_array($middlewares)) {
                throw new RuntimeException('Некорректная конфигурация промежуточного ПО');
            }
            foreach ($middlewares as $className) {
                if (method_exists($className, 'process')) {
                    static::$instances[$plugin][$appName][] = [$className, 'process'];
                } else {
                    // @todo Log
                    echo "Промежуточный $className::process не существует\n";
                }
            }
        }
    }

    /**
     * @param string $plugin
     * @param string $appName
     * @param bool $withGlobalMiddleware
     * @return array|mixed
     */
    public static function getMiddleware(string $plugin, string $appName, bool $withGlobalMiddleware = true)
    {
        // Глобальная midleware
        $globalMiddleware = $withGlobalMiddleware && isset(static::$instances[$plugin]['']) ? static::$instances[$plugin][''] : [];
        if ($appName === '') {
            return array_reverse($globalMiddleware);
        }
        // midleware приложения
        $appMiddleware = static::$instances[$plugin][$appName] ?? [];
        return array_reverse(array_merge($globalMiddleware, $appMiddleware));
    }
}
