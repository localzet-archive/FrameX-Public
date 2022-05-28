<?php

/**
 * @version     1.0.0-dev
 * @package     FrameX
 * @link        https://framex.localzet.ru
 * 
 * @author      localzet <creator@localzet.ru>
 * 
 * @copyright   Copyright (c) 2018-2020 Zorin Projects 
 * @copyright   Copyright (c) 2020-2022 NONA Team
 * 
 * @license     https://www.localzet.ru/license GNU GPLv3 License
 */

use support\Container;
use localzet\FrameX\Config;
use localzet\FrameX\Route;
use localzet\FrameX\Middleware;

$server = $server ?? null;

// Часовой пояс (если есть)
if ($timezone = config('app.default_timezone')) {
    date_default_timezone_set($timezone);
}

// Обработчик ошибок
set_error_handler(function ($level, $message, $file = '', $line = 0, $context = []) {
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
Config::reload(config_path(), ['route', 'container']);

// Запрашиваем плагины :))
foreach (config('plugin', []) as $firm => $projects) {
    foreach ($projects as $name => $project) {
        foreach ($project['autoload']['files'] ?? [] as $file) {
            include_once $file;
        }
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

// Ну и файлы автозагрузки
foreach (config('autoload.files', []) as $file) {
    include_once $file;
}

// Вот теперь грузим container из конфигурации
// Который мы так усердно пропускали вместе с route
// Container - загрузчик классов с хранилищем, дабы не создавать кучу экземпляров
$container = Container::instance();
Route::container($container);
Middleware::container($container);

// Загружаем промежуточное ПО
Middleware::load(config('middleware', []));

// Загружаем промежуточное ПО плагинов
foreach (config('plugin', []) as $firm => $projects) {
    foreach ($projects as $name => $project) {
        Middleware::load($project['middleware'] ?? []);
    }
}

// Загружаем статическое промежуточное ПО
Middleware::load(['__static__' => config('static.middleware', [])]);

// Запуск системы из конфигурации
foreach (config('bootstrap', []) as $class_name) {
    /** @var \localzet\FrameX\Bootstrap $class_name */
    $class_name::start($server);
}

// Запуск плагинов
foreach (config('plugin', []) as $firm => $projects) {
    foreach ($projects as $name => $project) {
        foreach ($project['bootstrap'] ?? [] as $class_name) {
            /** @var \localzet\FrameX\Bootstrap $class_name */
            $class_name::start($server);
        }
    }
}

Route::load(config_path());
