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

namespace support;

use Firebase\JWT\JWT as FJWT;
use Firebase\JWT\Key as FKEY;

class JWT
{
    private static $key;
    private static $alg;
    private static $iss;
    private static $aud;
    private static $exp;

    function __construct($config)
    {
        $date = new \DateTime();
        $date->setDate(date('Y') + 4, date('m'), date('d'));
        $exp = floor($date->format('U'));

        static::$key = $config['key'] ?? '256-bit-secret';
        static::$alg = $config['alg'] ?? 'HS256';
        static::$iss = $config['iss'] ?? 'Zorin Projects';
        static::$aud = $config['aud'] ?? 'FrameX';
        static::$exp = $config['exp'] ?? $exp;
    }

    public function encode($data)
    {
        try {
            $token = array(
                "iss" => static::$iss,
                "aud" => static::$aud,
                "iat" => floor(time()),
                "exp" => static::$exp,
                "data" => $data
            );

            return FJWT::encode($token, static::$key, static::$alg);
        } catch (\Exception $error) {
            return false;
        }
    }

    public function decode($token)
    {
        try {
            $data = FJWT::decode($token,  new FKEY(static::$key, static::$alg));
            if (
                $data
                && $data->iss == static::$iss
                && $data->aud == static::$aud
            ) {
                return (array) $data->data;
            } else {
                return false;
            }
        } catch (\Exception $error) {
            // TODO: ExpiredException
            return false;
        }
    }
}
