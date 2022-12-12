<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io/framex
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.ru>
 * @copyright   Copyright (c) 2018-2022 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
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
        $json = [
            'debug' => $this->_debug,
            'status' => $exception->getCode() ?? 500,
            'error' => $exception->getMessage(),
            'data' => $this->_debug ? \nl2br((string)$exception) : $exception->getMessage(),
        ];
        $this->_debug && $json['traces'] = (string)$exception;

        // Ответ JSON
        if ($request->expectsJson()) return responseJson($json);

        // DeAuthException - специализированный тип ошибок для деавторизации
        if ($exception instanceof DeAuthException && class_exists(\plugin\auth\app\middleware\Authentication::class) && config('plugin.auth.app.enabled', false) === true) {
            $error = empty($exception->getMessage()) ? null : $exception->getMessage();
            return \plugin\auth\app\middleware\Authentication::deauthorization($error);
        }

        return responseView('error', $json);
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
