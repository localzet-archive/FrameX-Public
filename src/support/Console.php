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

use support\console\Application;
use support\console\Command\Command as Commands;

class Console extends Application
{
    public function installCommands($path, $namspace = 'app\command')
    {
        $dir_iterator = new \RecursiveDirectoryIterator($path);
        $iterator = new \RecursiveIteratorIterator($dir_iterator);
        foreach ($iterator as $file) {
            if (is_dir($file)) {
                continue;
            }
            $class_name = $namspace . '\\' . basename($file, '.php');
            if (!is_a($class_name, Commands::class, true)) {
                continue;
            }
            $this->add(new $class_name);
        }
    }
}
