<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io/framex
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.ru>
 * @copyright   Copyright (c) 2018-2022 Localzet Group
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

    public static function vars()
    {
        $request = \request();
        $plugin =  $request->plugin ?? '';
        $handler = \config($plugin ? "plugin.$plugin.view.handler" : 'view.handler');
        return $handler::vars();
    }
}
