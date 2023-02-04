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

interface View
{
    /**
     * Render.
     * @param string $template
     * @param array $vars
     * @param string|null $app
     * @return string
     */
    static function render(string $template, array $vars, string $app = null): string;
}
