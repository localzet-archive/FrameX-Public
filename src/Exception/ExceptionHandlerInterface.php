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

namespace localzet\FrameX\Exception;

use Throwable;
use localzet\FrameX\Http\Request;
use localzet\FrameX\Http\Response;

interface ExceptionHandlerInterface
{
    /**
     * @param Throwable $exception
     * @return mixed
     */
    public function report(Throwable $exception);

    /**
     * @param Request $request
     * @param Throwable $exception
     * @return Response
     */
    public function render(Request $request, Throwable $exception): Response;
}
