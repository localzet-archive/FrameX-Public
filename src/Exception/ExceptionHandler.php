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

namespace localzet\FrameX\Exception;

use Throwable;
use Psr\Log\LoggerInterface;
use localzet\FrameX\Http\Request;
use localzet\FrameX\Http\Response;

/**
 * Class Handler
 * @package support\exception
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

        $this->_logger->error($exception->getMessage(), ['exception' => (string)$exception]);
    }

    /**
     * @param Request $request
     * @param Throwable $exception
     * @return Response
     */
    public function render(Request $request, Throwable $exception): Response
    {
        $code = $exception->getCode();
        if ($request->expectsJson()) {
            $json = ['code' => $code ? $code : 500, 'msg' => $exception->getMessage()];
            $this->_debug && $json['traces'] = (string)$exception;
            return new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
        }
        $error = $this->_debug ? nl2br((string)$exception) : 'Server internal error';
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
