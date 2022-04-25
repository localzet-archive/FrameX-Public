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

namespace support\bootstrap;

use localzet\FrameX\Bootstrap;
use localzet\V3\Protocols\Http;
use localzet\V3\Protocols\Http\Session as SessionBase;
use localzet\V3\Worker;

/**
 * Class Session
 * @package support
 */
class Session implements Bootstrap
{

    /**
     * @param Worker $worker
     * @return void
     */
    public static function start($worker)
    {
        $config = config('session');
        Http::sessionName($config['session_name']);
        SessionBase::handlerClass($config['handler'], $config['config'][$config['type']]);
        //session_set_cookie_params(0, $config['path'], $config['domain'], $config['secure'], $config['http_only']);
    }
}
