<?php

/**
 * @version     1.0.0-dev
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io
 * 
 * @author      localzet <creator@localzet.ru>
 * 
 * @copyright   Copyright (c) 2018-2020 Zorin Projects 
 * @copyright   Copyright (c) 2020-2022 NONA Team
 * 
 * @license     https://www.localzet.ru/license GNU GPLv3 License
 */

namespace support;

use Exception;
use localzet\JWT\JwtToken;

class JWT
{
    function __construct()
    {
        if (!class_exists('localzet\JWT\JwtToken')) {
            throw new Exception('Отсутствует пакет localzet/jwt');
        }
    }

    /**
     * Генерация токена
     * 
     * @param array $data $extend
     * @param array $payload ['aud' => '']
     * @return array ['token_type', 'expires_in', 'access_token', 'refresh_token'] 
     */
    public static function create(array $data, array $payload = [])
    {
        return JwtToken::generateToken($data, $payload);
    }

    /**
     * Проверка токена
     * 
     * @param int $type 1 - access, 2 - refresh
     * @param string $token 
     * @return array ['iss', 'exp', 'nbf', 'iat', 'extend' => []] 
     */
    public static function check(int $type = null, string $token = null)
    {
        return \localzet\JWT\JwtToken::verify($type, $token);
    }

    /**
     * Обновление токена
     * 
     * @param string $refresh_token
     * @return array ['access_token', 'refresh_token']
     */
    public static function refresh($refresh_token)
    {
        return \localzet\JWT\JwtToken::refreshToken($refresh_token);
    }

    /**
     * Получение данных из токена
     * 
     * @param string $key
     * @param int $type 1 - access, 2 - refresh
     * @param string $token 
     * @return array|mixed|string
     */
    public static function getData(string $key = null, int $type = null, string $token = null)
    {
        if (empty($key)) {
            return \localzet\JWT\JwtToken::getExtend($type, $token);
        } else {
            return \localzet\JWT\JwtToken::getExtendVal($key, $type, $token);
        }
    }

    /**
     * Осталось времени до истечения
     * 
     * @param string $key
     * @param int $type 1 - access, 2 - refresh
     * @param string $token 
     * @return $value 
     */
    public static function getExp(int $type = null, string $token = null)
    {
        return \localzet\JWT\JwtToken::getTokenExp($type, $token);
    }
}
