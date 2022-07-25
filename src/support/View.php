<?php

/**
 * @version     1.0.0-dev
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

class View
{
    public static function assign($name, $value = null)
    {
        static $handler;
        $handler = $handler ?: config('view.handler');
        $handler::assign($name, $value);
    }
}
