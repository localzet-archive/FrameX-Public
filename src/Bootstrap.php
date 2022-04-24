<?php

/**
 * @author    localzet<creator@localzet.ru>
 * @copyright localzet<creator@localzet.ru>
 * @link      https://www.localzet.ru/
 * @license   https://www.localzet.ru/license GNU GPLv3 License
 */

namespace localzet\FrameX;

use localzet\V3\Worker;

interface Bootstrap
{
    /**
     * onWorkerStart
     *
     * @param Worker $worker
     * @return mixed
     */
    public static function start($worker);
}
