<?php

namespace support\telegram\Objects\Payments;

use support\telegram\Objects\BaseObject;

/**
 * @link https://core.telegram.org/bots/api#orderinfo
 *
 * @property string|null          $name            (Optional). User name
 * @property string|null          $phoneNumber     (Optional). User's phone number
 * @property string|null          $email           (Optional). User email
 * @property ShippingAddress|null $shippingAddress (Optional). User shipping address
 */
class OrderInfo extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'shipping_address' => ShippingAddress::class,
        ];
    }
}
