<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io/framex
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.ru>
 * @copyright   Copyright (c) 2018-2022 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace localzet\FrameX\Http;

use localzet\FrameX\App;
use Throwable;

/**
 * Class Response
 */
class Response extends \localzet\Core\Protocols\Http\Response
{
    /**
     * @var Throwable
     */
    protected $_exception = null;

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
    public function file(string $file)
    {
        if ($this->notModifiedSince($file)) {
            return $this->withStatus(304);
        }
        return $this->withFile($file);
    }

    /**
     * @param string $file
     * @param string $download_name
     * @return $this
     */
    public function download(string $file, string $download_name = '')
    {
        $this->withFile($file);
        if ($download_name) {
            $this->header('Content-Disposition', "attachment; filename=\"$download_name\"");
        }
        return $this;
    }

    /**
     * @param string $file
     * @return bool
     */
    protected function notModifiedSince(string $file)
    {
        $if_modified_since = App::request()->header('if-modified-since');
        if ($if_modified_since === null || !($mtime = \filemtime($file))) {
            return false;
        }
        return $if_modified_since === \gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
    }

    /**
     * @param Throwable $exception
     * @return Throwable
     */
    public function exception($exception = null)
    {
        if ($exception) {
            $this->_exception = $exception;
        }
        return $this->_exception;
    }
}
