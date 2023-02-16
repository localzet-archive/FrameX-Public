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

use Fiber;
use SplObjectStorage;
use StdClass;
use WeakMap;
use Swow\Coroutine;
use localzet\Server\Events\Revolt;
use localzet\Server\Events\Swoole;
use localzet\Server\Events\Swow;
use localzet\Server\Server;

use function property_exists;

/**
 * Class Context
 */
class Context
{

    /**
     * @var SplObjectStorage|WeakMap
     */
    protected static $objectStorage;

    /**
     * @var StdClass
     */
    protected static $object;

    /**
     * @return StdClass
     */
    protected static function getObject(): StdClass
    {
        if (!static::$objectStorage) {
            static::$objectStorage = class_exists(WeakMap::class) ? new WeakMap() : new SplObjectStorage();
            static::$object = new StdClass;
        }
        $key = static::getKey();
        if (!isset(static::$objectStorage[$key])) {
            static::$objectStorage[$key] = new StdClass;
        }
        return static::$objectStorage[$key];
    }

    /**
     * @return mixed
     */
    protected static function getKey()
    {
        switch (Server::$eventLoopClass) {
            case Revolt::class:
                return Fiber::getCurrent();
            case Swoole::class:
                return \Swoole\Coroutine::getContext();
            case Swow::class:
                return Coroutine::getCurrent();
        }
        return static::$object;
    }

    /**
     * @param string|null $key
     * @return mixed
     */
    public static function get(string $key = null)
    {
        $obj = static::getObject();
        if ($key === null) {
            return $obj;
        }
        return $obj->$key ?? null;
    }

    /**
     * @param string $key
     * @param $value
     * @return void
     */
    public static function set(string $key, $value)
    {
        $obj = static::getObject();
        $obj->$key = $value;
    }

    /**
     * @param string $key
     * @return void
     */
    public static function delete(string $key)
    {
        $obj = static::getObject();
        unset($obj->$key);
    }

    /**
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        $obj = static::getObject();
        return property_exists($obj, $key);
    }

    /**
     * @return void
     */
    public static function destroy()
    {
        unset(static::$objectStorage[static::getKey()]);
    }
}
