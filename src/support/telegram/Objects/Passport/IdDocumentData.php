<?php

namespace support\telegram\Objects\Passport;

use support\telegram\Objects\BaseObject;

/**
 * @link https://core.telegram.org/bots/api#iddocumentdata
 *
 * @property string $documentNo    Document number
 * @property string|null $expiryDate (Optional). Date of expiry, in DD.MM.YYYY format
 */
class IdDocumentData extends BaseObject
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
