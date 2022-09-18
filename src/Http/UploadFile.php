<?php

/**
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

namespace localzet\FrameX\Http;

use localzet\FrameX\File;

/**
 * Class UploadFile
 */
class UploadFile extends File
{
    /**
     * @var string
     */
    protected $_uploadName = null;

    /**
     * @var string
     */
    protected $_uploadMimeType = null;

    /**
     * @var int
     */
    protected $_uploadErrorCode = null;

    /**
     * UploadFile constructor.
     *
     * @param string $file_name
     * @param string $upload_name
     * @param string $upload_mime_type
     * @param int $upload_error_code
     */
    public function __construct(string $file_name, string $upload_name, string $upload_mime_type, int $upload_error_code)
    {
        $this->_uploadName = $upload_name;
        $this->_uploadMimeType = $upload_mime_type;
        $this->_uploadErrorCode = $upload_error_code;
        parent::__construct($file_name);
    }

    /**
     * @return string
     */
    public function getUploadName()
    {
        return $this->_uploadName;
    }

    /**
     * @return string
     */
    public function getUploadMimeType()
    {
        return $this->_uploadMimeType;
    }

    /**
     * @deprecated
     * @return string
     */
    public function getUploadMineType()
    {
        return $this->_uploadMimeType;
    }

    /**
     * @return mixed
     */
    public function getUploadExtension()
    {
        return \pathinfo($this->_uploadName, PATHINFO_EXTENSION);
    }

    /**
     * @return int
     */
    public function getUploadErrorCode()
    {
        return $this->_uploadErrorCode;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->_uploadErrorCode === UPLOAD_ERR_OK;
    }
}
