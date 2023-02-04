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

class Install
{
    const FRAMEX_PLUGIN = true;

    /**
     * @var array
     */
    protected static $pathRelation = [
        'master' => 'master',
        'start.php' => 'start.php',
        'windows.php' => 'windows.php',
        'support/bootstrap.php' => 'support/bootstrap.php',
        'support/helpers.php' => 'support/helpers.php',
    ];

    /**
     * Install
     * @return void
     */
    public static function install()
    {
        static::installByRelation();
    }

    /**
     * Uninstall
     * @return void
     */
    public static function uninstall()
    {
    }

    /**
     * InstallByRelation
     * @return void
     */
    public static function installByRelation()
    {
        foreach (static::$pathRelation as $source => $dest) {
            if ($pos = strrpos($dest, '/')) {
                $parentDir = base_path() . '/' . substr($dest, 0, $pos);
                if (!is_dir($parentDir)) {
                    mkdir($parentDir, 0777, true);
                }
            }
            $sourceFile = __DIR__ . "/$source";
            copy_dir($sourceFile, base_path() . "/$dest", true);
            echo "Создан $dest\r\n";
            if (is_file($sourceFile)) {
                @unlink($sourceFile);
            }
        }
    }
}
