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

namespace support;

use Illuminate\Database\Capsule\Manager;

/**
 * Class Db
 * @method static array select(string $query, $bindings = [], $useReadPdo = true)
 * @method static int insert(string $query, $bindings = [])
 * @method static int update(string $query, $bindings = [])
 * @method static int delete(string $query, $bindings = [])
 * @method static bool statement(string $query, $bindings = [])
 * @method static mixed transaction(\Closure $callback, $attempts = 1)
 * @method static void beginTransaction()
 * @method static void rollBack($toLevel = null)
 * @method static void commit()
 * 
 * @deprecated
 */
class Db extends Manager
{
}
