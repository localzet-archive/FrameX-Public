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

namespace support\bootstrap;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;
use Illuminate\Events\Dispatcher;
use Illuminate\Pagination\Paginator;
use Jenssegers\Mongodb\Connection as MongodbConnection;
use support\Db;
use Throwable;
use localzet\FrameX\Bootstrap;
use localzet\Core\Timer;
use localzet\Core\Server;

/**
 * Class Laravel
 * @package support\Bootstrap
 */
class LaravelDb implements Bootstrap
{
    /**
     * @param Server $server
     *
     * @return void
     */
    public static function start($server)
    {
        if (!class_exists(Capsule::class)) {
            return;
        }

        $config = \config('database', []);
        $connections = $config['connections'] ?? [];
        if (!$connections) {
            return;
        }

        $capsule = new Capsule;

        $capsule->getDatabaseManager()->extend('mongodb', function ($config, $name) {
            $config['name'] = $name;
            return new MongodbConnection($config);
        });

        $default = $config['default'] ?? false;
        if ($default) {
            $default_config = $connections[$config['default']];
            $capsule->addConnection($default_config);
        }

        foreach ($connections as $name => $config) {
            $capsule->addConnection($config, $name);
        }

        if (\class_exists(Dispatcher::class)) {
            $capsule->setEventDispatcher(new Dispatcher(new Container));
        }

        $capsule->setAsGlobal();

        $capsule->bootEloquent();

        // Heartbeat
        if ($server) {
            Timer::add(55, function () use ($default, $connections, $capsule) {
                foreach ($capsule->getDatabaseManager()->getConnections() as $connection) {
                    /* @var \Illuminate\Database\MySqlConnection $connection **/
                    if ($connection->getConfig('driver') == 'mysql') {
                        try {
                            $connection->select('select 1');
                        } catch (Throwable $e) {}
                    }
                }
            });
        }

        // Paginator
        if (class_exists(Paginator::class)) {
            Paginator::queryStringResolver(function () {
                return request()->queryString();
            });
            Paginator::currentPathResolver(function () {
                return request()->path();
            });
            Paginator::currentPageResolver(function ($page_name = 'page') {
                $page = (int)request()->input($page_name, 1);
                return $page > 0 ? $page : 1;
            });
        }
    }
}
