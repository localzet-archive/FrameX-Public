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

use function array_diff;
use function array_map;
use function scandir;

/**
 * Class Util
 */
class Util
{
    /**
     * ScanDir.
     * @param string $basePath
     * @param bool $withBasePath
     * @return array
     */
    public static function scanDir(string $basePath, bool $withBasePath = true): array
    {
        if (!is_dir($basePath)) {
            return [];
        }
        $paths = array_diff(scandir($basePath), array('.', '..')) ?: [];
        return $withBasePath ? array_map(function ($path) use ($basePath) {
            return $basePath . DIRECTORY_SEPARATOR . $path;
        }, $paths) : $paths;
    }
}
