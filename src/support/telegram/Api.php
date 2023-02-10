<?php

namespace support\telegram;

use BadMethodCallException;
use Illuminate\Support\Traits\Macroable;
use support\telegram\Exceptions\TelegramSDKException;
use support\telegram\HttpClients\HttpClientInterface;

/**
 * Class Api.
 *
 * @mixin Commands\CommandBus
 */
class Api
{
    use Macroable {
        __call as macroCall;
    }

    use Events\EmitsEvents;

    use Traits\Http;
    use Traits\CommandsHandler;
    use Traits\HasContainer;

    use Methods\Chat;
    use Methods\Commands;
    use Methods\EditMessage;
    use Methods\Game;
    use Methods\Get;
    use Methods\Location;
    use Methods\Message;
    use Methods\Passport;
    use Methods\Payments;
    use Methods\Query;
    use Methods\Stickers;
    use Methods\Update;

    /** @var string Version number of the Telegram Bot PHP SDK. */
    const VERSION = '3.0.0';

    /** @var string The name of the environment variable that contains the Telegram Bot API Access Token. */
    const BOT_TOKEN_ENV_NAME = 'TELEGRAM_BOT_TOKEN';

    /**
     * Instantiates a new Telegram super-class object.
     *
     *
     * @param string                   $token             The Telegram Bot API Access Token.
     * @param bool                     $async             (Optional) Indicates if the request to Telegram will be asynchronous (non-blocking).
     *
     * @throws TelegramSDKException
     */
    public function __construct(string $accessToken, bool $async = false)
    {
        $this->accessToken = $accessToken;

        $this->validateAccessToken();
        $this->setAsyncRequest($async);
    }

    /**
     * Метод для обработки любых динамических методов.
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $arguments);
        }

        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $arguments);
        }

        // Если метод не существует в API - пробуем commandBus.
        if (preg_match('/^\w+Commands?/', $method, $matches)) {
            return call_user_func_array([$this->getCommandBus(), $matches[0]], $arguments);
        }

        throw new BadMethodCallException("Метод [$method] не существует.");
    }

    /**
     * @throws TelegramSDKException
     */
    private function validateAccessToken()
    {
        if (! $this->accessToken || ! is_string($this->accessToken)) {
            throw TelegramSDKException::tokenNotProvided(static::BOT_TOKEN_ENV_NAME);
        }
    }
}
