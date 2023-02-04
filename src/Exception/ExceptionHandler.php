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

use Psr\Log\LoggerInterface;
use Throwable;
use localzet\FrameX\Http\Request;
use localzet\FrameX\Http\Response;
use function json_encode;
use function nl2br;
use function trim;

/**
 * Class Handler
 */
class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger = null;

    /**
     * @var bool
     */
    protected $debug = false;

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
        $this->logger = $logger;
        $this->debug = $debug;
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
        if ($request = request()) {
            $logs = $request->getRealIp() . ' ' . $request->method() . ' ' . trim($request->fullUrl(), '/');
        }
        $this->logger->error($logs . PHP_EOL . $exception);

        // New report (Mongo) :)
        // 
        // $this->_logger->error($exception->getMessage(), [
        //     'debug' => $this->_debug,
        //     'ip' => $request->getRealIp(),
        //     'method' => $request->method(),
        //     'post' => $request->post(),
        //     'get' => $request->get(),
        //     'url' => \trim($request->fullUrl(), '/'),
        //     'exception' => [
        //         'code' => $exception->getCode() ?? 0,
        //         'file' => $exception->getFile(),
        //         'line' => $exception->getLine(),
        //         'message' => $exception->getMessage(),
        //         'previous' => $exception->getPrevious(),
        //         'trace' => $exception->getTrace(),
        //     ]
        // ]);
    }

    /**
     * @param Request $request
     * @param Throwable $exception
     * @return Response
     */
    public function render(Request $request, Throwable $exception): Response
    {
        $json = [
            'debug' => $this->debug,
            // Если получен DeAuthException => HTTP 401
            'status' => ($exception instanceof DeAuthException) ? 401 : ($exception->getCode() ?: 500),
            'error' => $exception->getMessage(),
            // 'data' => $this->debug ? \nl2br((string)$exception) : $exception->getMessage(),
        ];
        $this->debug && $json['traces'] = nl2br((string)$exception);
        $request->exception_id && $json['exception_id'] = $request->exception_id;

        // Ответ JSON
        if ($request->expectsJson()) return responseJson($json);

        !empty($json['exception_id']) && $json['error'] = "{$json['error']}. Обратитесь к администрации, код ошибки: {$json['exception_id']}";

        return responseView($json, 500);
    }

    /**
     * @param Throwable $e
     * @return bool
     */
    protected function shouldntReport(Throwable $e): bool
    {
        foreach ($this->dontReport as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }
        return false;
    }
    /**
     * Compatible $this->_debug
     *
     * @param string $name
     * @return bool|null
     */
    public function __get(string $name)
    {
        if ($name === '_debug') {
            return $this->debug;
        }
        return null;
    }
}
