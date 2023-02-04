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

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use function array_replace_recursive;
use function array_reverse;
use function count;
use function explode;
use function in_array;
use function is_array;
use function is_dir;
use function is_file;
use function key;
use function str_replace;

class Config
{

    /**
     * @var array
     */
    protected static $config = [];

    /**
     * @var string
     */
    protected static $configPath = '';

    /**
     * @var bool
     */
    protected static $loaded = false;

    /**
     * Загрузи
     * @param string $configPath
     * @param array $excludeFile
     * @param string|null $key
     * @return void
     */
    public static function load(string $configPath, array $excludeFile = [], string $key = null)
    {
        static::$configPath = $configPath;
        if (!$configPath) {
            return;
        }
        static::$loaded = false;
        $config = static::loadFromDir($configPath, $excludeFile);
        if (!$config) {
            static::$loaded = true;
            return;
        }
        if ($key !== null) {
            foreach (array_reverse(explode('.', $key)) as $k) {
                $config = [$k => $config];
            }
        }
        static::$config = array_replace_recursive(static::$config, $config);
        static::formatConfig();
        static::$loaded = true;
    }

    /**
     * Очистить
     * @return void
     */
    public static function clear()
    {
        static::$config = [];
    }

    /**
     * Форматировать
     * @return void
     */
    protected static function formatConfig()
    {
        $config = static::$config;
        // Merge log config
        foreach ($config['plugin'] ?? [] as $firm => $projects) {
            if (isset($projects['app'])) {
                foreach ($projects['log'] ?? [] as $key => $item) {
                    $config['log']["plugin.$firm.$key"] = $item;
                }
            }
            foreach ($projects as $name => $project) {
                if (!is_array($project)) {
                    continue;
                }
                foreach ($project['log'] ?? [] as $key => $item) {
                    $config['log']["plugin.$firm.$name.$key"] = $item;
                }
            }
        }
        // Merge database config
        foreach ($config['plugin'] ?? [] as $firm => $projects) {
            if (isset($projects['app'])) {
                foreach ($projects['database']['connections'] ?? [] as $key => $connection) {
                    $config['database']['connections']["plugin.$firm.$key"] = $connection;
                }
            }
            foreach ($projects as $name => $project) {
                if (!is_array($project)) {
                    continue;
                }
                foreach ($project['database']['connections'] ?? [] as $key => $connection) {
                    $config['database']['connections']["plugin.$firm.$name.$key"] = $connection;
                }
            }
        }
        if (!empty($config['database']['connections'])) {
            $config['database']['default'] = $config['database']['default'] ?? key($config['database']['connections']);
        }
        // Merge thinkorm config
        foreach ($config['plugin'] ?? [] as $firm => $projects) {
            if (isset($projects['app'])) {
                foreach ($projects['thinkorm']['connections'] ?? [] as $key => $connection) {
                    $config['thinkorm']['connections']["plugin.$firm.$key"] = $connection;
                }
            }
            foreach ($projects as $name => $project) {
                if (!is_array($project)) {
                    continue;
                }
                foreach ($project['thinkorm']['connections'] ?? [] as $key => $connection) {
                    $config['thinkorm']['connections']["plugin.$firm.$name.$key"] = $connection;
                }
            }
        }
        if (!empty($config['thinkorm']['connections'])) {
            $config['thinkorm']['default'] = $config['thinkorm']['default'] ?? key($config['thinkorm']['connections']);
        }
        // Merge redis config
        foreach ($config['plugin'] ?? [] as $firm => $projects) {
            if (isset($projects['app'])) {
                foreach ($projects['redis'] ?? [] as $key => $connection) {
                    $config['redis']["plugin.$firm.$key"] = $connection;
                }
            }
            foreach ($projects as $name => $project) {
                if (!is_array($project)) {
                    continue;
                }
                foreach ($project['redis'] ?? [] as $key => $connection) {
                    $config['redis']["plugin.$firm.$name.$key"] = $connection;
                }
            }
        }
        static::$config = $config;
    }

    /**
     * Загрузить из папки
     * @param string $configPath
     * @param array $excludeFile
     * @return array
     */
    public static function loadFromDir(string $configPath, array $excludeFile = []): array
    {
        $allConfig = [];
        $dirIterator = new RecursiveDirectoryIterator($configPath, FilesystemIterator::FOLLOW_SYMLINKS);
        $iterator = new RecursiveIteratorIterator($dirIterator);
        foreach ($iterator as $file) {
            /** var SplFileInfo $file */
            if (is_dir($file) || $file->getExtension() != 'php' || in_array($file->getBaseName('.php'), $excludeFile)) {
                continue;
            }
            $appConfigFile = $file->getPath() . '/app.php';
            if (!is_file($appConfigFile)) {
                continue;
            }
            $relativePath = str_replace($configPath . DIRECTORY_SEPARATOR, '', substr($file, 0, -4));
            $explode = array_reverse(explode(DIRECTORY_SEPARATOR, $relativePath));
            if (count($explode) >= 2) {
                $appConfig = include $appConfigFile;
                if (empty($appConfig['enable'])) {
                    continue;
                }
            }
            $config = include $file;
            foreach ($explode as $section) {
                $tmp = [];
                $tmp[$section] = $config;
                $config = $tmp;
            }
            $allConfig = array_replace_recursive($allConfig, $config);
        }
        return $allConfig;
    }

    /**
     * Получить
     * @param string|null $key
     * @param mixed $default
     * @return array|mixed|void|null
     */
    public static function get(string $key = null, $default = null)
    {
        if ($key === null) {
            return static::$config;
        }
        $keyArray = explode('.', $key);
        $value = static::$config;
        $found = true;
        foreach ($keyArray as $index) {
            if (!isset($value[$index])) {
                if (static::$loaded) {
                    return $default;
                }
                $found = false;
                break;
            }
            $value = $value[$index];
        }
        if ($found) {
            return $value;
        }
        return static::read($key, $default);
    }

    /**
     * Считать
     * @param string $key
     * @param mixed $default
     * @return array|mixed|null
     */
    protected static function read(string $key, $default = null)
    {
        $path = static::$configPath;
        if ($path === '') {
            return $default;
        }
        $keys = $keyArray = explode('.', $key);
        foreach ($keyArray as $index => $section) {
            unset($keys[$index]);
            if (is_file($file = "$path/$section.php")) {
                $config = include $file;
                return static::find($keys, $config, $default);
            }
            if (!is_dir($path = "$path/$section")) {
                return $default;
            }
        }
        return $default;
    }

    /**
     * Найти
     * @param array $keyArray
     * @param mixed $stack
     * @param mixed $default
     * @return array|mixed
     */
    protected static function find(array $keyArray, $stack, $default)
    {
        if (!is_array($stack)) {
            return $default;
        }
        $value = $stack;
        foreach ($keyArray as $index) {
            if (!isset($value[$index])) {
                return $default;
            }
            $value = $value[$index];
        }
        return $value;
    }

    /**
     * @param array $config
     */
    public static function set($config)
    {
        static::$config = array_replace_recursive(static::$config, $config);
    }
}
