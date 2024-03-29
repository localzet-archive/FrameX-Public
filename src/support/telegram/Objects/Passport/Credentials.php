<?php

namespace support\telegram\Objects\Passport;

use support\telegram\Objects\BaseObject;

/**
 * @link https://core.telegram.org/bots/api#credentials
 *
 * @property SecureData $secureData   Credentials for encrypted data
 * @property string     $nonce        Bot-specified nonce
 */
class Credentials extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'secure_data' => SecureData::class,
        ];
    }
}
