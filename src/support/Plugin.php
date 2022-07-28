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

class Plugin
{
    public static function install($event)
    {
        static::findHepler();
        $operation = $event->getOperation();
        $autoload = method_exists($operation, 'getPackage') ? $operation->getPackage()->getAutoload() : $operation->getTargetPackage()->getAutoload();
        if (!isset($autoload['psr-4'])) {
            return;
        }
        $namespace = key($autoload['psr-4']);
        $install_function = "\\{$namespace}Install::install";
        $plugin_const = "\\{$namespace}Install::FRAMEX_PLUGIN";
        if (defined($plugin_const) && is_callable($install_function)) {
            $install_function();
        }
    }

    public static function update($event)
    {
        static::install($event);
    }

    public static function uninstall($event)
    {
        static::findHepler();
        $autoload = $event->getOperation()->getPackage()->getAutoload();
        if (!isset($autoload['psr-4'])) {
            return;
        }
        $namespace = key($autoload['psr-4']);
        $uninstall_function = "\\{$namespace}Install::uninstall";
        $plugin_const = "\\{$namespace}Install::FRAMEX_PLUGIN";
        if (defined($plugin_const) && is_callable($uninstall_function)) {
            $uninstall_function();
        }
    }

    protected static function findHepler()
    {
        // Plugin.php in vendor
        $file = __DIR__ . '/../../../../../support/helpers.php';
        if (is_file($file)) {
            require_once $file;
            return;
        }
        // Plugin.php
        require_once __DIR__ . '/helpers.php';
    }
}
