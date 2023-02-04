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

use localzet\FrameX\File;
use function pathinfo;

/**
 * Class UploadFile
 */
class UploadFile extends File
{
    /**
     * @var string
     */
    protected $uploadName = null;

    /**
     * @var string
     */
    protected $uploadMimeType = null;

    /**
     * @var int
     */
    protected $uploadErrorCode = null;

    /**
     * UploadFile constructor.
     *
     * @param string $fileName
     * @param string $uploadName
     * @param string $uploadMimeType
     * @param int $uploadErrorCode
     */
    public function __construct(string $fileName, string $uploadName, string $uploadMimeType, int $uploadErrorCode)
    {
        $this->uploadName = $uploadName;
        $this->uploadMimeType = $uploadMimeType;
        $this->uploadErrorCode = $uploadErrorCode;
        parent::__construct($fileName);
    }

    /**
     * @return string
     */
    public function getUploadName(): ?string
    {
        return $this->uploadName;
    }

    /**
     * @return string
     */
    public function getUploadMimeType(): ?string
    {
        return $this->uploadMimeType;
    }

    /**
     * @return string
     */
    public function getUploadExtension(): string
    {
        return pathinfo($this->uploadName, PATHINFO_EXTENSION);
    }

    /**
     * @return int
     */
    public function getUploadErrorCode(): ?int
    {
        return $this->uploadErrorCode;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->uploadErrorCode === UPLOAD_ERR_OK;
    }

}
