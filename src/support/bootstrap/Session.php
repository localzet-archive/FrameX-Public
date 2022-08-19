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

namespace support\bootstrap;

use localzet\FrameX\Bootstrap;
use localzet\Core\Protocols\Http;
use localzet\Core\Protocols\Http\Session as SessionBase;
use localzet\Core\Server;

/**
 * Class Session
 */
class Session implements Bootstrap
{

    /**
     * @param Server $server
     * @return void
     */
    public static function start($server)
    {
        $config = \config('session');
        if (\property_exists(SessionBase::class, 'name')) {
            SessionBase::$name = $config['session_name'];
        } else {
            Http::sessionName($config['session_name']);
        }
        SessionBase::handlerClass($config['handler'], $config['config'][$config['type']]);
        $map = [
            'auto_update_timestamp' => 'autoUpdateTimestamp',
            'cookie_lifetime' => 'cookieLifetime',
            'gc_probability' => 'gcProbability',
            'cookie_path' => 'cookiePath',
            'http_only' => 'httpOnly',
            'same_site' => 'sameSite',
            'lifetime' => 'lifetime',
            'domain' => 'domain',
            'secure' => 'secure',
        ];
        foreach ($map as $key => $name) {
            if (isset($config[$key]) && \property_exists(SessionBase::class, $name)) {
                SessionBase::${$name} = $config[$key];
            }
        }
    }
}
