<?php
/**
 * @author    localzet<creator@localzet.ru>
 * @copyright localzet<creator@localzet.ru>
 * @link      https://www.localzet.ru/
 * @license   https://www.localzet.ru/license GNU GPLv3 License
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