<?php

namespace support\telegram;

use support\telegram\Api;
use support\telegram\Objects\Update;
use support\telegram\Objects\Chat;

/**
 * Абстрактный контроллер Telegram-бота
 * 
 * **Использование:**
 *      1. !!! controller_reuse => false !!!
 *      2. Задай $accessToken и $async
 *      2. Настрой WebHook @see https://api.telegram.org/bot{TOKEN}/setWebhook?url={URL}
 */
abstract class TelegramBotController
{
    /**
     * Telegram Bot API
     * 
     * @var null|\support\telegram\Api $api
     */
    public ?Api $api;

    /**
     * Новое событие
     * 
     * @var null|\support\telegram\Objects\Update $update
     * @see https://core.telegram.org/bots/api#update
     */
    public ?Update $update;

    /**
     * Текущий чат
     * 
     * @var null|\support\telegram\Objects\Chat $chat
     * @see https://core.telegram.org/bots/api#chat
     */
    public ?Chat $chat;

    /**
     * Тип пришедшего события
     * 
     * @var string $type
     */
    public string $type;

    /**
     * Токен бота
     * 
     * @var string $accessToken
     */
    public string $accessToken;

    /**
     * Выполнять запрросы асинхронно?
     * 
     * @var bool $async
     */
    public bool $async = true;

    public function __construct()
    {
        $this->api = new Api($this->accessToken, $this->async);
        $this->update = $this->api->getWebhookUpdate();

        $this->chat = $this->update->getChat();
        $this->type = $this->update->objectType();
    }

    /**
     * Отправить сообщение
     * 
     * <code>
     * $options = [
     *       'parse_mode'                  => '',  // string     - (Optional). Send Markdown or HTML, if you want Telegram apps to show bold, italic, fixed-width text or inline URLs in your bot's message.
     *       'entities'                    => '',  // array      - (Optional). List of special entities that appear in the caption, which can be specified instead of parse_mode
     *       'disable_web_page_preview'    => '',  // bool       - (Optional). Disables link previews for links in this message
     *       'protect_content'             => '',  // bool       - (Optional). Protects the contents of the sent message from forwarding and saving
     *       'disable_notification'        => '',  // bool       - (Optional). Sends the message silently. iOS users will not receive a notification, Android users will receive a notification with no sound.
     *       'reply_to_message_id'         => '',  // int        - (Optional). If the message is a reply, ID of the original message
     *       'allow_sending_without_reply' => '',  // bool       - (Optional). Pass True, if the message should be sent even if the specified replied-to message is not found
     *       'reply_markup'                => '',  // object     - (Optional). One of either InlineKeyboardMarkup|ReplyKeyboardMarkup|ReplyKeyboardRemove|ForceReply for an inline keyboard, custom reply keyboard, instructions to remove reply keyboard or to force a reply from the user.
     * ]
     * </code>
     * 
     * @link https://core.telegram.org/bots/api#sendmessage
     * 
     * @param string $text
     * @param int $chat_id
     * @param array $options
     *
     * @throws TelegramSDKException
     *
     * @return MessageObject

     */
    public function send($text, $chat_id = null, array $options = [])
    {
        if (empty($chat_id)) $chat_id = $this->chat->getId();

        $result = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => !empty($options['parse_mode']) ? $options['parse_mode'] : 'HTML',
        ];

        $this->api->sendMessage($result + $options);
        return $this->response();
    }

    public function response($text = '')
    {
        return new \support\Response(200, [], $text);
    }
}
