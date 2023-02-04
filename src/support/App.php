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

namespace support;

use Dotenv\Dotenv;
use RuntimeException;
use support\http\Curl;
use localzet\FrameX\Config;
use localzet\FrameX\Util;
use localzet\Core\Connection\TcpConnection;
use localzet\Core\Server;
use function base_path;
use function call_user_func;
use function is_dir;
use function opcache_get_status;
use function opcache_invalidate;
use const DIRECTORY_SEPARATOR;

class App
{
    /**
     * @return void
     */
    public static function run()
    {
        ini_set('display_errors', 'on');

        if (class_exists(Dotenv::class) && file_exists(run_path('.env'))) {
            if (method_exists(Dotenv::class, 'createUnsafeImmutable')) {
                Dotenv::createUnsafeImmutable(run_path())->load();
            } else {
                Dotenv::createMutable(run_path())->load();
            }
        }

        static::loadAllConfig(['route', 'container']);

        $errorReporting = config('app.error_reporting', E_ALL);
        if (isset($errorReporting)) {
            error_reporting($errorReporting);
        }
        if ($timezone = config('app.default_timezone')) {
            date_default_timezone_set($timezone);
        }

        $runtimeLogsPath = runtime_path() . DIRECTORY_SEPARATOR . 'logs';
        if (!file_exists($runtimeLogsPath) || !is_dir($runtimeLogsPath)) {
            if (!mkdir($runtimeLogsPath, 0777, true)) {
                throw new RuntimeException("Failed to create runtime logs directory. Please check the permission.");
            }
        }

        $runtimeViewsPath = runtime_path() . DIRECTORY_SEPARATOR . 'views';
        if (!file_exists($runtimeViewsPath) || !is_dir($runtimeViewsPath)) {
            if (!mkdir($runtimeViewsPath, 0777, true)) {
                throw new RuntimeException("Failed to create runtime views directory. Please check the permission.");
            }
        }

        Server::$onMasterReload = function () {
            if (function_exists('opcache_get_status')) {
                if ($status = opcache_get_status()) {
                    if (isset($status['scripts']) && $scripts = $status['scripts']) {
                        foreach (array_keys($scripts) as $file) {
                            opcache_invalidate($file, true);
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
            $propertyMap = [
                'name',
                'count',
                'user',
                'group',
                'reusePort',
                'transport',
                'protocol'
            ];
            foreach ($propertyMap as $property) {
                if (isset($config[$property])) {
                    $server->$property = $config[$property];
                }
            }

            $server->onServerStart = function ($server) {
                if ($connected = @fsockopen("www.example.com", 80)) {
                    $is_conn = true;
                    fclose($connected);
                } else {
                    $is_conn = false;
                    Config::set(['app' => [
                        'core_version' => WEBCORE_VERSION,
                        'engine_version' => WEBKIT_VERSION,
                        'version' => FRAMEX_VERSION,
                    ]]);
                }

                if (class_exists(Curl::class) && $is_conn) {
                    $http = new Curl();

                    // Ядро (WebCore) - Сервер
                    $core_version = $http->request('https://repo.packagist.org/p2/localzet/core.json', 'GET');
                    $core_version = json_decode($core_version, true);

                    // Механика (FrameX (FX) Engine) - Фреймворк
                    $engine_version = $http->request('https://repo.packagist.org/p2/localzet/framex.json', 'GET');
                    $engine_version = json_decode($engine_version, true);

                    // Окружение (WebKit) - Приложение
                    $version = $http->request('https://repo.packagist.org/p2/localzet/webkit.json', 'GET');
                    $version = json_decode($version, true);

                    Config::set(['app' => [
                        'core_version' => $core_version['packages']['localzet/core'][0]['version'],
                        'engine_version' => $engine_version['packages']['localzet/framex'][0]['version'],
                        'version' => $version['packages']['localzet/webkit'][0]['version'],
                    ]]);
                }

                require_once base_path() . '/support/bootstrap.php';
                $app = new \localzet\FrameX\App(config('app.request_class', Request::class), Log::channel('default'), app_path(), public_path());
                $server->onMessage = [$app, 'onMessage'];
                \call_user_func([$app, 'onServerStart'], $server);
            };

            $server->onServerReload = function ($server) {
                if ($connected = @fsockopen("www.example.com", 80)) {
                    $is_conn = true;
                    fclose($connected);
                } else {
                    $is_conn = false;
                    Config::set(['app' => [
                        'core_version' => WEBCORE_VERSION,
                        'engine_version' => WEBKIT_VERSION,
                        'version' => FRAMEX_VERSION,
                    ]]);
                }

                if (class_exists(Curl::class) && $is_conn) {
                    $http = new Curl();

                    // Ядро (WebCore) - Сервер
                    $core_version = $http->request('https://repo.packagist.org/p2/localzet/core.json', 'GET');
                    $core_version = json_decode($core_version, true);

                    // Механика (FrameX (FX) Engine) - Фреймворк
                    $engine_version = $http->request('https://repo.packagist.org/p2/localzet/framex.json', 'GET');
                    $engine_version = json_decode($engine_version, true);

                    // Окружение (WebKit) - Приложение
                    $version = $http->request('https://repo.packagist.org/p2/localzet/webkit.json', 'GET');
                    $version = json_decode($version, true);

                    Config::set(['app' => [
                        'core_version' => $core_version['packages']['localzet/core'][0]['version'],
                        'engine_version' => $engine_version['packages']['localzet/framex'][0]['version'],
                        'version' => $version['packages']['localzet/webkit'][0]['version'],
                    ]]);
                }
            };
        }

        // Windows does not support custom processes.
        if (DIRECTORY_SEPARATOR === '/') {
            foreach (config('process', []) as $processName => $config) {
                server_start($processName, $config);
            }
            foreach (config('plugin', []) as $firm => $projects) {
                foreach ($projects as $name => $project) {
                    if (!is_array($project)) {
                        continue;
                    }
                    foreach ($project['process'] ?? [] as $processName => $config) {
                        server_start("plugin.$firm.$name.$processName", $config);
                    }
                }
                foreach ($projects['process'] ?? [] as $processName => $config) {
                    server_start("plugin.$firm.$processName", $config);
                }
            }
        }

        if (!defined('GLOBAL_START')) {
            Server::runAll();
        }
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
            if (is_dir($dir)) {
                Config::load($dir, $excludes, "plugin.$name");
            }
        }
    }
}
