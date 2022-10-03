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

namespace Webman\Session;

use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\RouteCollector;
use localzet\Core\Protocols\Http\Session\RedisSessionHandler as RedisHandler;

/**
 * Class FileSessionHandler
 * @package Webman
 */
class RedisSessionHandler extends RedisHandler
{
}
