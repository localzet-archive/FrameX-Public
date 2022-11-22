<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io/framex
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.ru>
 * @copyright   Copyright (c) 2018-2022 Localzet Group
 * @license     https://www.localzet.ru/license GNU GPLv3 License
 */

namespace localzet\FrameX;

use localzet\FrameX\Exception\FileException;

class File extends \SplFileInfo
{

    /**
     * @param string $destination
     * @return File
     */
    public function move(string $destination)
    {
        \set_error_handler(function ($type, $msg) use (&$error) {
            $error = $msg;
        });
        $path = \pathinfo($destination, PATHINFO_DIRNAME);
        if (!\is_dir($path) && !\mkdir($path, 0777, true)) {
            \restore_error_handler();
            throw new FileException(\sprintf('Unable to create the "%s" directory (%s)', $path, \strip_tags($error)));
        }
        if (!rename($this->getPathname(), $destination)) {
            \restore_error_handler();
            throw new FileException(\sprintf('Could not move the file "%s" to "%s" (%s)', $this->getPathname(), $destination, \strip_tags($error)));
        }
        \restore_error_handler();
        @\chmod($destination, 0666 & ~\umask());
        return new self($destination);
    }
}
