<?php

namespace support\telegram\Objects;

/**
 * Class PollOption.
 *
 * @link https://core.telegram.org/bots/api#polloption
 *
 * @property string $text           Option text, 1-100 characters
 * @property int    $voterCount     Number of users that voted for this option
 */
class PollOption extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [];
    }
}
