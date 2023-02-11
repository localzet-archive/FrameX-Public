<?php

namespace support\telegram\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use support\telegram\BotsManager;

/**
 * Class Telegram.
 *
 * @method static list<\support\telegram\Api> getBots(string $name)
 * @method static \support\telegram\Api bot(string|null $name)
 * @method static \support\telegram\Api reconnect(string|null $name)
 * @method static \support\telegram\BotsManager disconnect(string|null $name)
 *
 * @mixin \support\telegram\BotsManager
 */
class Telegram extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BotsManager::class;
    }
}
