#!/usr/bin/env php
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

require_once __DIR__ . '/vendor/autoload.php';

use localzet\Core\Server;
use localzet\Core\Protocols\Http;
use localzet\Core\Connection\TcpConnection;

use localzet\FrameX\App;
use localzet\FrameX\Config;
use localzet\FrameX\Route;
use localzet\FrameX\Middleware;

use support\Request;
use support\Log;
use support\Container;

// Отображать ошибки
ini_set('display_errors', 'on');
error_reporting(E_ALL);

// Загрузить конфигурацию
Config::load(config_path(), ['route', 'container']);

// Часовой пояс (если есть)
if ($timezone = config('app.default_timezone')) {
    date_default_timezone_set($timezone);
}

// Рабочая папка логов
$runtime_logs_path = runtime_path() . DIRECTORY_SEPARATOR . 'logs';
if (!file_exists($runtime_logs_path) || !is_dir($runtime_logs_path)) {
    if (!mkdir($runtime_logs_path, 0777, true)) {
        throw new \RuntimeException("Не удалось создать рабочую папку логов. Пожалуйста, проверьте права.");
    }
}

// Рабочая папка шаблонов
$runtime_views_path = runtime_path() . DIRECTORY_SEPARATOR . 'views';
if (!file_exists($runtime_views_path) || !is_dir($runtime_views_path)) {
    if (!mkdir($runtime_views_path, 0777, true)) {
        throw new \RuntimeException("Не удалось создать рабочую папку шаблонов. Пожалуйста, проверьте права.");
    }
}

// Обнуление кэша при перезагрузке воркера
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

// Конфигурация для воркера
$config = config('server');
Server::$pidFile = $config['pid_file'];
Server::$stdoutFile = $config['stdout_file'];
Server::$logFile = $config['log_file'];
Server::$eventLoopClass = $config['event_loop'] ?? '';
TcpConnection::$defaultMaxPackageSize = $config['max_package_size'] ?? 10 * 1024 * 1024;
if (property_exists(Server::class, 'statusFile')) {
    Server::$statusFile = $config['status_file'] ?? '';
}

// Прослушка воркера
if ($config['listen']) {
    // Запуск
    $server = new Server($config['listen'], $config['context']);
    if ($config['storage']['enable'] === true && class_exists(\localzet\Storage\Server::class)) {
        $storage = new \localzet\Storage\Server($config['storage']['ip'], $config['storage']['port']);
    }

    // Назначение конфигурации
    $property_map = [
        'name',
        'count',
        'user',
        'group',
        'reusePort',
        'transport',
    ];

    foreach ($property_map as $property) {
        if (isset($config[$property])) {
            $server->$property = $config[$property];
        }
    }

    // Запуск приложения при старте сервера
    $server->onServerStart = function ($server) {
        if (class_exists(\localzet\HTTP\Client::class)) {
            $http = new localzet\HTTP\Client();

            $http->get('https://api.github.com/repos/localzet/Core/releases/latest', function ($response) {
                $data = json_decode($response->getBody(), true);
                Config::set(['app' => ['version' => $data['name']]]);
            });
        }

        require_once base_path() . '/support/bootstrap.php';
        $app = new App($server, Container::instance(), Log::channel('default'), app_path(), public_path());
        Http::requestClass(config('app.request_class', config('server.request_class', Request::class)));
        $server->onMessage = [$app, 'onMessage'];
    };
}

// Винда не поддерживает кастомные процессы ::>_<::
if (\DIRECTORY_SEPARATOR === '/') {
    foreach (config('process', []) as $process_name => $config) {
        server_start($process_name, $config);
    }
    foreach (config('plugin', []) as $firm => $projects) {
        foreach ($projects as $name => $project) {
            foreach ($project['process'] ?? [] as $process_name => $config) {
                server_start("plugin.$firm.$name.$process_name", $config);
            }
        }
    }
}

// Запуск движка))
Server::runAll();
