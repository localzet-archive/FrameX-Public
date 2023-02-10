<?php

namespace support\telegram\Objects\Passport;

use support\telegram\Objects\BaseObject;

/**
 * @link https://core.telegram.org/bots/api#filecredentials
 *
 * @property string  $fileHash     Checksum of encrypted file
 * @property string  $secret       Secret of encrypted file
 */
class FileCredentials extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
        ];
    }
}
