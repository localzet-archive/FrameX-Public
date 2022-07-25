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

namespace support\exception;

use localzet\FrameX\Http\Request;
use localzet\FrameX\Http\Response;
use Throwable;
use localzet\FrameX\Exception\ExceptionHandler;

/**
 * Class Handler
 * @package support\exception
 */
class Handler extends ExceptionHandler
{
    public $dontReport = [
        BusinessException::class,
    ];

    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    public function render(Request $request, Throwable $exception): Response
    {
        return parent::render($request, $exception);
    }
}
