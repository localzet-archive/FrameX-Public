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

namespace support\bootstrap;

use localzet\FrameX\Bootstrap;
use localzet\Core\Protocols\Http;
use localzet\Core\Protocols\Http\Session as SessionBase;
use localzet\Core\Server;

/**
 * Class Session
 * @package support
 */
class Session implements Bootstrap
{

    /**
     * @param Server $server
     * @return void
     */
    public static function start($server)
    {
        $config = config('session');
        Http::sessionName($config['session_name']);
        SessionBase::handlerClass($config['handler'], $config['config'][$config['type']]);
        //session_set_cookie_params(0, $config['path'], $config['domain'], $config['secure'], $config['http_only']);
    }
}
