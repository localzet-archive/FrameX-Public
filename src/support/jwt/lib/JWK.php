<?php

namespace support\jwt\lib;

use DomainException;
use InvalidArgumentException;
use UnexpectedValueException;

/**
 * JSON Web Key
 * @see https://tools.ietf.org/html/draft-ietf-jose-json-web-key-41
 *
 * @category Authentication
 * @package  Authentication_JWT
 * @author   Ivan Zorin <zorin@localzet.com>
 * @author   Bui Sy Nguyen <nguyenbs@gmail.com>
 */
class JWK
{
    private const OID = '1.2.840.10045.2.1';
    private const ASN1_OBJECT_IDENTIFIER = 0x06;
    private const ASN1_SEQUENCE = 0x10; // также включён в JWT
    private const ASN1_BIT_STRING = 0x03;
    private const EC_CURVES = [
        'P-256' => '1.2.840.10045.3.1.7', // Len: 64
        // 'P-384' => '1.3.132.0.34', // Len: 96 (еще не поддерживается)
        // 'P-521' => '1.3.132.0.35', // Len: 132 (не поддерживается)
    ];

    /** Разобрать набор ключей JWK
     *
     * @param array<mixed> $jwks Набор веб-ключей JSON в виде ассоциативного массива
     * @param string       $defaultAlg Алгоритм по-умолчанию для объекта Key
     *
     * @return array<string, Key> Ассоциативный массив идентификаторов ключей (детский) для ключевых объектов
     *
     * @throws InvalidArgumentException     Набор JWK пуст
     * @throws UnexpectedValueException     Набор JWK недействителен
     * @throws DomainException              Сбой OpenSSL
     *
     * @uses parseKey
     */
    public static function parseKeySet(array $jwks, string $defaultAlg = null): array
    {
        $keys = [];

        if (!isset($jwks['keys'])) {
            throw new UnexpectedValueException('Элемент "keys" должен существовать в наборе JWK');
        }

        if (empty($jwks['keys'])) {
            throw new InvalidArgumentException('В наборе JWK не было ключей');
        }

        foreach ($jwks['keys'] as $k => $v) {
            $kid = isset($v['kid']) ? $v['kid'] : $k;
            if ($key = self::parseKey($v, $defaultAlg)) {
                $keys[(string) $kid] = $key;
            }
        }

        if (0 === \count($keys)) {
            throw new UnexpectedValueException('Поддерживаемые алгоритмы в наборе JWK не найдены');
        }

        return $keys;
    }

    /** Разобрать ключ JWK
     *
     * @param array<mixed> $jwk Индивидуальный JWK
     * @param string       $defaultAlg Алгоритм по-умолчанию для объекта Key
     *
     * @return Key Ключевой объект для JWK
     *
     * @throws InvalidArgumentException     JWK пуст
     * @throws UnexpectedValueException     JWK недействителен
     * @throws DomainException              Сбой OpenSSL
     *
     * @uses createPemFromModulusAndExponent
     */
    public static function parseKey(array $jwk, string $defaultAlg = null): ?Key
    {
        if (empty($jwk)) {
            throw new InvalidArgumentException('Необходимо указать JWK.');
        }

        if (!isset($jwk['kty'])) {
            throw new UnexpectedValueException('JWK должен содержать параметр kty.');
        }

        if (!isset($jwk['alg'])) {
            if (\is_null($defaultAlg)) {
                /**
                 * Параметр "alg" является необязательным в KTY, но требуется алгоритм
                 * для разбора в этой библиотеке. Используйте параметр $defaultAlg при анализе
                 * набора ключей для предотвращения этой ошибки.
                 * @see https://datatracker.ietf.org/doc/html/rfc7517#section-4.4
                 */
                throw new UnexpectedValueException('JWK должен содержать параметр "alg"');
            }
            $jwk['alg'] = $defaultAlg;
        }

        switch ($jwk['kty']) {
            case 'RSA':
                if (!empty($jwk['d'])) {
                    throw new UnexpectedValueException('Закрытые ключи RSA не поддерживаются');
                }
                if (!isset($jwk['n']) || !isset($jwk['e'])) {
                    throw new UnexpectedValueException('Ключи RSA должны содержать значения как для «n», так и для «e».');
                }

                $pem = self::createPemFromModulusAndExponent($jwk['n'], $jwk['e']);
                $publicKey = \openssl_pkey_get_public($pem);
                if (false === $publicKey) {
                    throw new DomainException(
                        'Ошибка OpenSSL: ' . \openssl_error_string()
                    );
                }
                return new Key($publicKey, $jwk['alg']);
            case 'EC':
                if (isset($jwk['d'])) {
                    // The key is actually a private key
                    throw new UnexpectedValueException('Ключевые данные должны быть для открытого ключа');
                }

                if (empty($jwk['crv'])) {
                    throw new UnexpectedValueException('crv не задан');
                }

                if (!isset(self::EC_CURVES[$jwk['crv']])) {
                    throw new DomainException('Нераспознанная или неподдерживаемая кривая EC');
                }

                if (empty($jwk['x']) || empty($jwk['y'])) {
                    throw new UnexpectedValueException('х и у не установлены');
                }

                $publicKey = self::createPemFromCrvAndXYCoordinates($jwk['crv'], $jwk['x'], $jwk['y']);
                return new Key($publicKey, $jwk['alg']);
            default:
                // В настоящее время поддерживается только RSA
                break;
        }

        return null;
    }

    /** Преобразует значения EC JWK в формат pem.
     *
     * @param   string  $crv Кривая EC (поддерживается только P-256)
     * @param   string  $x   EC X-Координата
     * @param   string  $y   EC Y-Координата
     *
     * @return  string
     */
    private static function createPemFromCrvAndXYCoordinates(string $crv, string $x, string $y): string
    {
        $pem =
            self::encodeDER(
                self::ASN1_SEQUENCE,
                self::encodeDER(
                    self::ASN1_SEQUENCE,
                    self::encodeDER(
                        self::ASN1_OBJECT_IDENTIFIER,
                        self::encodeOID(self::OID)
                    )
                        . self::encodeDER(
                            self::ASN1_OBJECT_IDENTIFIER,
                            self::encodeOID(self::EC_CURVES[$crv])
                        )
                ) .
                    self::encodeDER(
                        self::ASN1_BIT_STRING,
                        \chr(0x00) . \chr(0x04)
                            . JWT::urlsafeB64Decode($x)
                            . JWT::urlsafeB64Decode($y)
                    )
            );

        return sprintf(
            "-----BEGIN PUBLIC KEY-----\n%s\n-----END PUBLIC KEY-----\n",
            wordwrap(base64_encode($pem), 64, "\n", true)
        );
    }

    /** Создать открытый ключ, представленный в формате PEM, из информации о модуле и экспоненте RSA.
     *
     * @param string $n Модуль RSA, закодированный в Base64
     * @param string $e Экспонента RSA, закодированная в Base64
     *
     * @return string Открытый ключ RSA, представленный в формате PEM
     *
     * @uses encodeLength
     */
    private static function createPemFromModulusAndExponent(
        string $n,
        string $e
    ): string {
        $mod = JWT::urlsafeB64Decode($n);
        $exp = JWT::urlsafeB64Decode($e);

        $modulus = \pack('Ca*a*', 2, self::encodeLength(\strlen($mod)), $mod);
        $publicExponent = \pack('Ca*a*', 2, self::encodeLength(\strlen($exp)), $exp);

        $rsaPublicKey = \pack(
            'Ca*a*a*',
            48,
            self::encodeLength(\strlen($modulus) + \strlen($publicExponent)),
            $modulus,
            $publicExponent
        );

        // sequence(oid(1.2.840.113549.1.1.1), null)) = rsaEncryption.
        $rsaOID = \pack('H*', '300d06092a864886f70d0101010500'); // шестнадцатеричная версия MA0GCSqGSIb3DQEBAQUA
        $rsaPublicKey = \chr(0) . $rsaPublicKey;
        $rsaPublicKey = \chr(3) . self::encodeLength(\strlen($rsaPublicKey)) . $rsaPublicKey;

        $rsaPublicKey = \pack(
            'Ca*a*',
            48,
            self::encodeLength(\strlen($rsaOID . $rsaPublicKey)),
            $rsaOID . $rsaPublicKey
        );

        return "-----BEGIN PUBLIC KEY-----\r\n" .
            \chunk_split(\base64_encode($rsaPublicKey), 64) .
            '-----END PUBLIC KEY-----';
    }

    /** DER-кодировать длину
     *
     * DER поддерживает длину до (2**8)**127, однако мы будем поддерживать только длину до (2**8)**4.
     * @link http://itu.int/ITU-T/studygroups/com17/languages/X.690-0207.pdf#p=13 X.690 параграф 8.1.3
     *
     * @param int $length
     * @return string
     */
    private static function encodeLength(int $length): string
    {
        if ($length <= 0x7F) {
            return \chr($length);
        }

        $temp = \ltrim(\pack('N', $length), \chr(0));

        return \pack('Ca*', 0x80 | \strlen($temp), $temp);
    }

    /**
     * Кодирует значение в объект DER.
     * Также включён в localzet\JWT\JWT
     *
     * @param   int     $type Тег DER
     * @param   string  $value Значение для кодирования
     * @return  string  Закодированный объект
     */
    private static function encodeDER(int $type, string $value): string
    {
        $tag_header = 0;
        if ($type === self::ASN1_SEQUENCE) {
            $tag_header |= 0x20;
        }

        // Тип
        $der = \chr($tag_header | $type);

        // Длина
        $der .= \chr(\strlen($value));

        return $der . $value;
    }

    /** Кодирует строку в OID в кодировке DER.
     *
     * @param   string $oid Строка OID
     * @return  string Двоичный OID в кодировке DER
     */
    private static function encodeOID(string $oid): string
    {
        $octets = explode('.', $oid);

        // Получить первый октет
        $first = (int) array_shift($octets);
        $second = (int) array_shift($octets);
        $oid = \chr($first * 40 + $second);

        // Перебирать последующие октеты
        foreach ($octets as $octet) {
            if ($octet == 0) {
                $oid .= \chr(0x00);
                continue;
            }
            $bin = '';

            while ($octet) {
                $bin .= \chr(0x80 | ($octet & 0x7f));
                $octet >>= 7;
            }
            $bin[0] = $bin[0] & \chr(0x7f);

            // Преобразование в прямой порядок байтов, если необходимо
            if (pack('V', 65534) == pack('L', 65534)) {
                $oid .= strrev($bin);
            } else {
                $oid .= $bin;
            }
        }

        return $oid;
    }
}
