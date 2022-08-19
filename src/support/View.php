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

class View
{
    /**
     * @param mixed $name
     * @param mixed $value
     * @return void
     */
    public static function assign($name, $value = null)
    {
        $request = \request();
        $plugin =  $request->plugin ?? '';
        $handler = \config($plugin ? "plugin.$plugin.view.handler" : 'view.handler');
        $handler::assign($name, $value);
    }
}
