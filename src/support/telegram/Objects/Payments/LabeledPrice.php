<?php

namespace support\telegram\Objects\Payments;

use support\telegram\Objects\BaseObject;

/**
 * @link https://core.telegram.org/bots/api#labeledprice
 *
 * @property string $label               Portion label
 * @property int    $amount              Price of the product in the smallest units of the currency (integer, not float/double).
 */
class LabeledPrice extends BaseObject
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
