<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io/framex
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.ru>
 * @copyright   Copyright (c) 2018-2022 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace localzet\FrameX\Session;

use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\RouteCollector;
use localzet\Core\Protocols\Http\Session\FileSessionHandler as FileHandler;

/**
 * Class FileSessionHandler
 */
class FileSessionHandler extends FileHandler
{

}