<?php

namespace support\telegram\Events;

use League\Event\AbstractEvent;
use support\telegram\Api;
use support\telegram\Objects\Update;

final class UpdateEvent extends AbstractEvent
{
    public const NAME = 'update';

    /**
     * @deprecated Will be removed in SDK v4
     *
     * @var string
     */
    private $name;

    /** @var \support\telegram\Api */
    public $telegram;

    /** @var \support\telegram\Objects\Update */
    public $update;

    public function __construct(Api $telegram, Update $update, string $name = self::NAME)
    {
        $this->telegram = $telegram;
        $this->update = $update;
        $this->name = $name;
    }

    /** {@inheritDoc} */
    public function getName(): string
    {
        return $this->name;
    }
}
