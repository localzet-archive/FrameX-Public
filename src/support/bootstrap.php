<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io/framex
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.ru>
 * @copyright   Copyright (c) 2018-2022 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

use support\Log;
use localzet\FrameX\Bootstrap;
use localzet\FrameX\Config;
use localzet\FrameX\Route;
use localzet\FrameX\Middleware;
use localzet\FrameX\Util;

$server = $server ?? null;

// Часовой пояс (если есть)
if ($timezone = config('app.default_timezone')) {
    date_default_timezone_set($timezone);
}

// Обработчик ошибок
set_error_handler(function ($level, $message, $file = '', $line = 0) {
    if (error_reporting() & $level) {
        throw new ErrorException($message, 0, $level, $file, $line);
    }
});

// Костыль, но работает
// Если начинаешь падать - тупо жди 1 секунду и продолжай работать
if ($server) {
    register_shutdown_function(function ($start_time) {
        if (time() - $start_time <= 1) {
            sleep(1);
        }
    }, time());
}

// Перезапросить конфигурацию
support\App::loadAllConfig(['route']);

foreach (config('autoload.files', []) as $file) {
    include_once $file;
}

foreach (glob(\base_path() . '/autoload/*.php') as $file) {
    include_once($file);
}

// Запрашиваем плагины :))
foreach (config('plugin', []) as $firm => $projects) {
    foreach ($projects as $name => $project) {
        if (!is_array($project)) {
            continue;
        }
        foreach ($project['autoload']['files'] ?? [] as $file) {
            include_once $file;
        }
    }
    foreach ($projects['autoload']['files'] ?? [] as $file) {
        include_once $file;
    }
}
// ['plugin' => [
//     'firm' => [
//         'name' => [
//             // Конфигурация
//             'middleware' => [
//                 'app1' => [
//                     'Class1',
//                     'Class2',
//                     'Class3'
//                 ],
//                 'app2' => [
//                     'Class1',
//                     'Class2',
//                     'Class3'
//                 ]
//             ],
//             'process' => [
//                 'process_name' => [
//                     'listen',
//                     'context',
//                     'count',
//                     'user',
//                     'group',
//                     'reloadable',
//                     'reusePort',
//                     'transport',
//                     'protocol',
//                     'handler',
//                     'constructor'
//                 ]
//             ],
//             'autoload' => [
//                 'files' => [
//                     'file1',
//                     'file2',
//                     'file3'
//                 ]
//             ]
//         ]
//     ]
// ]];

Middleware::load(config('middleware', []), '');

// Загружаем промежуточное ПО плагинов
foreach (config('plugin', []) as $firm => $projects) {
    foreach ($projects as $name => $project) {
        if (!is_array($project) || $name === 'static') {
            continue;
        }
        Middleware::load($project['middleware'] ?? [], '');
    }
    Middleware::load($projects['middleware'] ?? [], $firm);
    Middleware::load($projects['global_middleware'] ?? [], '');
    if ($static_middlewares = config("plugin.$firm.static.middleware")) {
        Middleware::load(['__static__' => $static_middlewares], $firm);
    }
}

// Загружаем статическое промежуточное ПО
Middleware::load(['__static__' => config('static.middleware', [])], '');

// Запуск системы из конфигурации
foreach (config('bootstrap', []) as $class_name) {
    if (!class_exists($class_name)) {
        $log = "Warning: Class $class_name setting in config/bootstrap.php not found\r\n";
        echo $log;
        Log::error($log);
        continue;
    }
    /** @var \localzet\FrameX\Bootstrap $class_name */
    $class_name::start($server);
}

// Запуск плагинов
foreach (config('plugin', []) as $firm => $projects) {
    foreach ($projects as $name => $project) {
        if (!is_array($project)) {
            continue;
        }
        foreach ($project['bootstrap'] ?? [] as $class_name) {
            if (!class_exists($class_name)) {
                $log = "Warning: Class $class_name setting in config/plugin/$firm/$name/bootstrap.php not found\r\n";
                echo $log;
                Log::error($log);
                continue;
            }
            /** @var \localzet\FrameX\Bootstrap $class_name */
            $class_name::start($server);
        }
    }
    foreach ($projects['bootstrap'] ?? [] as $class_name) {
        if (!class_exists($class_name)) {
            $log = "Warning: Class $class_name setting in plugin/$firm/config/bootstrap.php not found\r\n";
            echo $log;
            Log::error($log);
            continue;
        }
        /** @var Bootstrap $class_name */
        $class_name::start($server);
    }
}

$directory = base_path() . '/plugin';
$paths = [config_path()];
foreach (Util::scanDir($directory) as $path) {
    if (is_dir($path = "$path/config")) {
        $paths[] = $path;
    }
}
Route::load($paths);
