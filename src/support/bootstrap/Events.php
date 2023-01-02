<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io/framex
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.ru>
 * @copyright   Copyright (c) 2018-2022 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace support\bootstrap;

use localzet\FrameX\Bootstrap;
use support\Container;
use support\Event;
use support\Log;


class Events implements Bootstrap
{
    /**
     * @var array
     */
    protected static $events = [];

    /**
     * @param Server $server
     * @return void
     */
    public static function start($server)
    {
        if (empty(config('event')) && is_array(config('event')) && !empty(config('event.app.enable'))) {
            $events = [];
            foreach (config('event') as $event_name => $callbacks) {
                $callbacks = static::convertCallable($callbacks);
                if (is_callable($callbacks)) {
                    $events[$event_name] = [$callbacks];
                    Event::on($event_name, $callbacks);
                    continue;
                }
                if (!is_array($callbacks)) {
                    $msg = "Events: $event_name => " . var_export($callbacks, true) . " is not callable\n";
                    echo $msg;
                    Log::error($msg);
                    continue;
                }
                foreach ($callbacks as $callback) {
                    $callback = static::convertCallable($callback);
                    if (is_callable($callback)) {
                        $events[$event_name][] = $callback;
                        Event::on($event_name, $callback);
                        continue;
                    }
                    $msg = "Events: $event_name => " . var_export($callback, true) . " is not callable\n";
                    echo $msg;
                    Log::error($msg);
                }
            }
            static::$events = array_merge_recursive(static::$events, $events);
        }
    }

    protected static function convertCallable($callback)
    {
        if (\is_array($callback)) {
            $callback = \array_values($callback);
            if (isset($callback[1]) && \is_string($callback[0]) && \class_exists($callback[0])) {
                $callback = [Container::get($callback[0]), $callback[1]];
            }
        }
        return $callback;
    }
}
