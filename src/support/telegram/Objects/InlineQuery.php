<?php

namespace support\telegram\Objects;

/**
 * Class InlineQuery.
 *
 *
 * @property int           $id         Unique identifier for this query.
 * @property User          $from       Sender.
 * @property Location|null $location   (Optional). Sender location, only for bots that request user location.
 * @property string        $query      Text of the query.
 * @property string        $offset     Offset of the results to be returned.
 * @property string|null   $chatType   (Optional). Type of the chat, from which the inline query was sent. Can be either “sender” for a private chat with the inline query sender, “private”, “group”, “supergroup”, or “channel”. The chat type should be always known for requests sent from official clients and most third-party clients, unless the request was sent from a secret chat
 *
 * @link https://core.telegram.org/bots/api#inlinequery
 */
class InlineQuery extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'from'     => User::class,
            'location' => Location::class,
        ];
    }

    public function objectType(): ?string
    {
        return $this->findType(['location']);
    }
}
