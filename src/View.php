<?php

/**
 * @version     1.0.0-dev
 * @package     FrameX
 * @link        https://framex.localzet.ru
 * 
 * @author      localzet <creator@localzet.ru>
 * 
 * @copyright   Copyright (c) 2018-2020 Zorin Projects 
 * @copyright   Copyright (c) 2020-2022 NONA Team
 * 
 * @license     https://www.localzet.ru/license GNU GPLv3 License
 */

namespace localzet\FrameX;

interface View
{
    /**
     * @param $template
     * @param $vars
     * @param null $app
     * @return string
     */
    static function render($template, $vars, $app = null);
}
