<?php

/**
 * @version     1.0.0-dev
 * @package     FrameX
 * @link        https://framex.localzet.ru
 * 
 * @author      localzet <creator@localzet.ru>
 * 
 * @copyright   Copyright (c) 2018-2020 Zorin Projects 
 * @copyright   Copyright (c) 2020-2022 NONA Team
 * 
 * @license     https://www.localzet.ru/license GNU GPLv3 License
 */

namespace support\bot;

class Telegram extends \Telegram\Bot\Api
{
    function __construct($config)
    {
        $this->accessToken = $config['token'] ?? getenv(static::BOT_TOKEN_ENV_NAME);
        $this->validateAccessToken();

        $this->setAsyncRequest(true);

        $this->httpClientHandler = null;
    }
}
