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

namespace support\bootstrap;

use localzet\FrameX\Bootstrap;
use localzet\Core\Protocols\Http;
use localzet\Core\Protocols\Http\Session as SessionBase;
use localzet\Core\Server;
use function config;
use function property_exists;

/**
 * Class Session
 */
class Session implements Bootstrap
{

    /**
     * @param Server|null $server
     * @return void
     */
    public static function start(?Server $server)
    {
        $config = config('session');
        if (property_exists(SessionBase::class, 'name')) {
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
            if (isset($config[$key]) && property_exists(SessionBase::class, $name)) {
                SessionBase::${$name} = $config[$key];
            }
        }
    }
}
