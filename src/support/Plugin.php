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

class Plugin
{
    /**
     * @param $event
     * @return void
     */
    public static function install($event)
    {
        static::findHepler();
        $operation = $event->getOperation();
        $autoload = \method_exists($operation, 'getPackage') ? $operation->getPackage()->getAutoload() : $operation->getTargetPackage()->getAutoload();
        if (!isset($autoload['psr-4'])) {
            return;
        }
        foreach ($autoload['psr-4'] as $namespace => $path) {
            $install_function = "\\{$namespace}Install::install";
            $plugin_const = "\\{$namespace}Install::FRAMEX_PLUGIN";
            if (\defined($plugin_const) && \is_callable($install_function)) {
                $install_function();
            }
        }
    }

    /**
     * @param $event
     * @return void
     */
    public static function update($event)
    {
        static::install($event);
    }

    /**
     * @param $event
     * @return void
     */
    public static function uninstall($event)
    {
        static::findHepler();
        $autoload = $event->getOperation()->getPackage()->getAutoload();
        if (!isset($autoload['psr-4'])) {
            return;
        }
        foreach ($autoload['psr-4'] as $namespace => $path) {
            $uninstall_function = "\\{$namespace}Install::uninstall";
            $plugin_const = "\\{$namespace}Install::FRAMEX_PLUGIN";
            if (defined($plugin_const) && is_callable($uninstall_function)) {
                $uninstall_function();
            }
        }
    }

    /**
     * @return void
     */
    protected static function findHepler()
    {
        // Plugin.php in vendor
        $file = __DIR__ . '/../../../../../support/helpers.php';
        if (\is_file($file)) {
            require_once $file;
            return;
        }
        // Plugin.php
        require_once __DIR__ . '/helpers.php';
    }
}
