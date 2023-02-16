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

namespace localzet\FrameX\Http;

use Throwable;
use localzet\FrameX\App;
use function filemtime;
use function gmdate;


/**
 * Class Response
 */
class Response extends \localzet\Server\Protocols\Http\Response
{
    /**
     * @var Throwable
     */
    protected $exception = null;

    function __construct(
        $status = 200,
        $headers = array(),
        $body = ''
    ) {
        $headers = $headers + config('app.headers', []);
        parent::__construct($status, $headers, $body);
    }
    /**
     * @param string $file
     * @return $this
     */
    public function file(string $file): Response
    {
        if ($this->notModifiedSince($file)) {
            return $this->withStatus(304);
        }
        return $this->withFile($file);
    }

    /**
     * @param string $file
     * @param string $downloadName
     * @return $this
     */
    public function download(string $file, string $downloadName = ''): Response
    {
        $this->withFile($file);
        if ($downloadName) {
            $this->header('Content-Disposition', "attachment; filename=\"$downloadName\"");
        }
        return $this;
    }

    /**
     * @param string $file
     * @return bool
     */
    protected function notModifiedSince(string $file): bool
    {
        $ifModifiedSince = App::request()->header('if-modified-since');
        if ($ifModifiedSince === null || !($mtime = filemtime($file))) {
            return false;
        }
        return $ifModifiedSince === gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
    }

    /**
     * @param Throwable|null $exception
     * @return Throwable|null
     */
    public function exception(Throwable $exception = null): ?Throwable
    {
        if ($exception) {
            $this->exception = $exception;
        }
        return $this->exception;
    }
}
