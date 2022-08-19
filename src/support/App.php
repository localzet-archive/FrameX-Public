<?php

namespace support;

use Dotenv\Dotenv;

use localzet\FrameX\Config;
use localzet\FrameX\Util;

use localzet\Core\Connection\TcpConnection;
use localzet\Core\Protocols\Http;
use localzet\Core\Server;

class App
{
    /**
     * @return void
     */
    public static function run()
    {
        ini_set('display_errors', 'on');
        error_reporting(E_ALL);

        if (class_exists(Dotenv::class) && file_exists(base_path() . '/.env')) {
            if (method_exists(Dotenv::class, 'createUnsafeImmutable')) {
                Dotenv::createUnsafeImmutable(base_path())->load();
            } else {
                Dotenv::createMutable(base_path())->load();
            }
        }

        static::loadAllConfig(['route', 'container']);

        $error_reporting = config('app.error_reporting');
        if (isset($error_reporting)) {
            error_reporting($error_reporting);
        }
        if ($timezone = config('app.default_timezone')) {
            date_default_timezone_set($timezone);
        }

        $runtime_logs_path = runtime_path() . DIRECTORY_SEPARATOR . 'logs';
        if (!file_exists($runtime_logs_path) || !is_dir($runtime_logs_path)) {
            if (!mkdir($runtime_logs_path, 0777, true)) {
                throw new \RuntimeException("Failed to create runtime logs directory. Please check the permission.");
            }
        }

        $runtime_views_path = runtime_path() . DIRECTORY_SEPARATOR . 'views';
        if (!file_exists($runtime_views_path) || !is_dir($runtime_views_path)) {
            if (!mkdir($runtime_views_path, 0777, true)) {
                throw new \RuntimeException("Failed to create runtime views directory. Please check the permission.");
            }
        }

        Server::$onMasterReload = function () {
            if (function_exists('opcache_get_status')) {
                if ($status = \opcache_get_status()) {
                    if (isset($status['scripts']) && $scripts = $status['scripts']) {
                        foreach (array_keys($scripts) as $file) {
                            \opcache_invalidate($file, true);
                        }
                    }
                }
            }
        };

        $config = config('server');
        Server::$pidFile = $config['pid_file'];
        Server::$stdoutFile = $config['stdout_file'];
        Server::$logFile = $config['log_file'];
        Server::$eventLoopClass = $config['event_loop'] ?? '';
        TcpConnection::$defaultMaxPackageSize = $config['max_package_size'] ?? 10 * 1024 * 1024;
        if (property_exists(Server::class, 'statusFile')) {
            Server::$statusFile = $config['status_file'] ?? '';
        }
        if (property_exists(Server::class, 'stopTimeout')) {
            Server::$stopTimeout = $config['stop_timeout'] ?? 2;
        }

        if ($config['listen']) {
            $server = new Server($config['listen'], $config['context']);
            $property_map = [
                'name',
                'count',
                'user',
                'group',
                'reusePort',
                'transport',
                'protocol'
            ];
            foreach ($property_map as $property) {
                if (isset($config[$property])) {
                    $server->$property = $config[$property];
                }
            }

            $server->onServerStart = function ($server) {
                require_once \base_path() . '/support/bootstrap.php';
                $app = new \localzet\FrameX\App(config('app.request_class', Request::class), Log::channel('default'), app_path(), public_path());
                $server->onMessage = [$app, 'onMessage'];
                \call_user_func([$app, 'onServerStart'], $server);
            };
        }

        // Windows does not support custom processes.
        if (\DIRECTORY_SEPARATOR === '/') {
            foreach (config('process', []) as $process_name => $config) {
                server_start($process_name, $config);
            }
            foreach (config('plugin', []) as $firm => $projects) {
                foreach ($projects as $name => $project) {
                    if (!is_array($project)) {
                        continue;
                    }
                    foreach ($project['process'] ?? [] as $process_name => $config) {
                        server_start("plugin.$firm.$name.$process_name", $config);
                    }
                }
                foreach ($projects['process'] ?? [] as $process_name => $config) {
                    server_start("plugin.$firm.$process_name", $config);
                }
            }
        }

        Server::runAll();
    }

    /**
     * @param array $excludes
     * @return void
     */
    public static function loadAllConfig(array $excludes = [])
    {
        Config::load(config_path(), $excludes);
        $directory = base_path() . '/plugin';
        foreach (Util::scanDir($directory, false) as $name) {
            $dir = "$directory/$name/config";
            if (\is_dir($dir)) {
                Config::load($dir, $excludes, "plugin.$name");
            }
        }
    }
}