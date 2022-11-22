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

use localzet\Core\Server;

interface Bootstrap
{
    /**
     * onServerStart
     *
     * @param Server $server
     * @return mixed
     */
    public static function start($server);
}
