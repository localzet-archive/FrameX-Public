<?php

namespace support\telegram\Objects;

/**
 * Class MessageAutoDeleteTimerChanged.
 *
 * @link https://core.telegram.org/bots/api#messageautodeletetimerchanged
 *
 * @property int $message_auto_delete_time  New auto-delete time for messages in the chat; in seconds
 */
class MessageAutoDeleteTimerChanged extends BaseObject
{
    public function relations()
    {
        return [];
    }
}
