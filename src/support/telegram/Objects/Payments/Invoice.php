<?php

namespace support\telegram\Objects\Payments;

use support\telegram\Objects\BaseObject;

/**
 * @link https://core.telegram.org/bots/api#invoice
 *
 * @property string $title                    Product name
 * @property string $description              Product description
 * @property string $startParameter           Unique bot deep-linking parameter that can be used to generate this invoice
 * @property string $currency                 Three-letter ISO 4217 currency code
 * @property int    $totalAmount              Total price in the smallest units of the currency (integer, not float/double)
 */
class Invoice extends BaseObject
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
