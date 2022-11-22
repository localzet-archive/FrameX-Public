<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io/framex
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.ru>
 * @copyright   Copyright (c) 2018-2022 RootX Group
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
    static function render(string $template, array $vars, string $app = null);
}
