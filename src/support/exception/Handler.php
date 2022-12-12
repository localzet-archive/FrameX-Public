<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io/framex
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.ru>
 * @copyright   Copyright (c) 2018-2022 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace support\exception;

use Throwable;
use localzet\FrameX\Exception\ExceptionHandler;
use localzet\FrameX\Http\Request;
use localzet\FrameX\Http\Response;

/**
 * Class Handler
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
        if (($exception instanceof BusinessException) && ($response = $exception->render($request))) {
            return $response;
        }

        return parent::render($request, $exception);
    }
}
