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

use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\RouteCollector;
use localzet\Core\Protocols\Http\Session\FileSessionHandler as SessionHandler;

/**
 * Class FileSessionHandler
 * @package localzet\FrameX
 */
class FileSessionHandler extends SessionHandler
{
}
