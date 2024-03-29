<?php

namespace support\telegram\Objects;

/**
 * Class ProximityAlertTriggered.
 *
 * @link https://core.telegram.org/bots/api#proximityalerttriggered
 *
 * @property User $traveler  User that triggered the alert
 * @property User $watcher   User that set the alert
 * @property int  $distance  The distance between the users
 */
class ProximityAlertTriggered extends BaseObject
{
    public function relations(): array
    {
        return [
            'user' => User::class,
        ];
    }
}
