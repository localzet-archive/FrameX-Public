<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io/framex
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.ru>
 * @copyright   Copyright (c) 2018-2022 Localzet Group
 * @license     https://www.localzet.ru/license GNU GPLv3 License
 */

namespace localzet\FrameX\Exception;

use Psr\Log\LoggerInterface;
use Throwable;
use localzet\FrameX\Http\Request;
use localzet\FrameX\Http\Response;

/**
 * Class Handler
 */
class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    protected $_logger = null;

    /**
     * @var bool
     */
    protected $_debug = false;

    /**
     * @var array
     */
    public $dontReport = [];

    /**
     * ExceptionHandler constructor.
     * @param $logger
     * @param $debug
     */
    public function __construct($logger, $debug)
    {
        $this->_logger = $logger;
        $this->_debug = $debug;
    }

    /**
     * @param Throwable $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        if ($this->shouldntReport($exception)) {
            return;
        }

        $logs = '';
        if ($request = \request()) {
            $logs = $request->getRealIp() . ' ' . $request->method() . ' ' . \trim($request->fullUrl(), '/');
        }
        $this->_logger->error($logs . PHP_EOL . $exception);
    }

    /**
     * @param Request $request
     * @param Throwable $exception
     * @return Response
     */
    public function render(Request $request, Throwable $exception): Response
    {
        $status = $exception->getCode();
        // if ($request->expectsJson()) {
        $json = [
            'debug' => $this->_debug,
            'status' => $status ? $status : 500,
            'error' => $this->_debug ? $exception->getMessage() : 'Ошибка сервера'
        ];
        $this->_debug && $json['traces'] = (string)$exception;
        return new Response(
            200,
            ['Content-Type' => 'application/json'],
            \json_encode($json, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
        );
        // }
        $error = $this->_debug ? \nl2br((string)$exception) : ($this->_debug ? $exception->getMessage() : 'Ошибка сервера');
        return new Response(500, [], $error);
    }

    /**
     * @param Throwable $e
     * @return bool
     */
    protected function shouldntReport(Throwable $e)
    {
        foreach ($this->dontReport as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }
        return false;
    }
}
