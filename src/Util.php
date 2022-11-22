<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io/framex
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.ru>
 * @copyright   Copyright (c) 2018-2022 Localzet Group
 * @license     https://www.localzet.ru/license GNU GPLv3 License
 */

namespace localzet\FrameX;

/**
 * Class Util
 */
class Util
{
    /**
     * @param string $path
     * @return array
     */
    public static function scanDir(string $base_path, $with_base_path = true): array
    {
        if (!is_dir($base_path)) {
            return [];
        }
        $paths = \array_diff(\scandir($base_path), array('.', '..')) ?: [];
        return $with_base_path ? \array_map(function ($path) use ($base_path) {
            return $base_path . DIRECTORY_SEPARATOR . $path;
        }, $paths) : $paths;
    }
}
