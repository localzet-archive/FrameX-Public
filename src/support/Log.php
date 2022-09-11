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

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;

/**
 * Class Log
 *
 * @method static void log($level, $message, array $context = [])
 * @method static void debug($message, array $context = [])
 * @method static void info($message, array $context = [])
 * @method static void notice($message, array $context = [])
 * @method static void warning($message, array $context = [])
 * @method static void error($message, array $context = [])
 * @method static void critical($message, array $context = [])
 * @method static void alert($message, array $context = [])
 * @method static void emergency($message, array $context = [])
 */
class Log
{
    /**
     * @var array
     */
    protected static $_instance = [];

    /**
     * @param string $name
     * @return Logger
     */
    public static function channel(string $name = 'default')
    {
        if (!isset(static::$_instance[$name])) {
            $preconfig = [];
            foreach (config('plugin', []) as $firm => $projects) {
                foreach ($projects as $name => $project) {
                    if (!is_array($project) || $name === 'static') {
                        continue;
                    }
                    $preconfig += $project['log'] ?? [];
                }
            }
            $preconfig += \config('log', []);
            $config = $preconfig[$name];
            $handlers = self::handlers($config);
            $processors = self::processors($config);
            static::$_instance[$name] = new Logger($name, $handlers, $processors);
        }
        return static::$_instance[$name];
    }


    /**
     * @param array $config
     * @return array
     */
    protected static function handlers(array $config): array
    {
        $handlerConfigs = $config['handlers'] ?? [[]];
        $handlers = [];
        foreach ($handlerConfigs as $value) {
            $class = $value['class'] ?? [];
            $constructor = $value['constructor'] ?? [];

            $formatterConfig = $value['formatter'] ?? [];

            $class && $handlers[] = self::handler($class, $constructor, $formatterConfig);
        }

        return $handlers;
    }

    /**
     * @param string $class
     * @param array $constructor
     * @param array $formatterConfig
     * @return HandlerInterface
     */
    protected static function handler(string $class, array $constructor, array $formatterConfig): HandlerInterface
    {
        /** @var HandlerInterface $handler */
        $handler = new $class(...\array_values($constructor));

        if ($handler instanceof FormattableHandlerInterface && $formatterConfig) {
            $formatterClass = $formatterConfig['class'];
            $formatterConstructor = $formatterConfig['constructor'];

            /** @var FormatterInterface $formatter */
            $formatter = new $formatterClass(...\array_values($formatterConstructor));

            $handler->setFormatter($formatter);
        }

        return $handler;
    }

    /**
     * @param array $config
     * @return array
     */
    protected static function processors(array $config): array
    {
        $result = [];
        if (!isset($config['processors']) && isset($config['processor'])) {
            $config['processors'] = [$config['processor']];
        }

        foreach ($config['processors'] ?? [] as $value) {
            if (\is_array($value) && isset($value['class'])) {
                $value = new $value['class'](...\array_values($value['constructor'] ?? []));;
            }

            $result[] = $value;
        }

        return $result;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return static::channel('default')->{$name}(...$arguments);
    }
}
