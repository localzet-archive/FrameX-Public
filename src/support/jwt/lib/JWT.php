<?php

namespace support\jwt\lib;

use DateTime;
use stdClass;
use ArrayAccess;

use OpenSSLAsymmetricKey;
use OpenSSLCertificate;

use Exception;
use DomainException;
use InvalidArgumentException;
use UnexpectedValueException;

use support\jwt\Exception\BeforeValidException;
use support\jwt\Exception\ExpiredException;
use support\jwt\Exception\SignatureInvalidException;

/**
 * JSON Web Token
 * @see https://tools.ietf.org/html/rfc7519
 *
 * @category Authentication
 * @package  Authentication_JWT
 * @author   Ivan Zorin <zorin@localzet.com>
 * @author   Neuman Vong <neuman@twilio.com>
 * @author   Anant Narayanan <anant@php.net>
 */
class JWT
{
    private const ASN1_INTEGER = 0x02;
    private const ASN1_SEQUENCE = 0x10;
    private const ASN1_BIT_STRING = 0x03;

    /**
     * Погрешность во времени при проверке nbf, iat или exp
     *
     * @var int
     */
    public static $leeway = 0;

    /**
     * Разрешить указывать текущую временную метку.
     * Полезно для фиксации значения в модульном тестировании.
     * По умолчанию используется значение PHP time(), если оно равно null.
     *
     * @var ?int
     */
    public static $timestamp = null;

    /**
     * @var array<string, string[]>
     */
    public static $supported_algs = [
        'ES384' => ['openssl', 'SHA384'],
        'ES256' => ['openssl', 'SHA256'],
        'HS256' => ['hash_hmac', 'SHA256'],
        'HS384' => ['hash_hmac', 'SHA384'],
        'HS512' => ['hash_hmac', 'SHA512'],
        'RS256' => ['openssl', 'SHA256'],
        'RS384' => ['openssl', 'SHA384'],
        'RS512' => ['openssl', 'SHA512'],
        'EdDSA' => ['sodium_crypto', 'EdDSA'],
    ];

    /** Декодирует строку JWT в объект PHP.
     *
     * @param string                 $jwt           JWT
     * @param Key|array<string,Key>  $keyOrKeyArray  Ключ или ассоциативный массив идентификаторов ключей (kid) для объектов Key.
     *                                               Если используемый алгоритм асимметричен, это открытый ключ
     *                                               Каждый объект Key содержит алгоритм и соответствующий ключ.
     *                                               Поддерживаемые алгоритмы: 'ES384','ES256', 'HS256', 'HS384',
     *                                               'HS512', 'RS256', 'RS384', and 'RS512'
     * @param bool                   $timeException  Проверять ли на соотетствие временным ограничениям?
     *
     * @return stdClass Полезная нагрузка JWT как объект PHP
     *
     * @throws InvalidArgumentException     Ключ пуст или некорректен
     * @throws DomainException              JWT имеет неверный формат
     * @throws UnexpectedValueException     JWT некорректен
     * @throws SignatureInvalidException    Ошибка проверки подписи
     * @throws BeforeValidException         Использование JWT до даты, указанной в «nbf»
     * @throws BeforeValidException         Использование JWT до даты, указанной в «iat»
     * @throws ExpiredException             Использование JWT после даты, указанной в «exp»
     *
     * @uses jsonDecode
     * @uses urlsafeB64Decode
     */
    public static function decode(
        string $jwt,
        $keyOrKeyArray,
        bool $timeException = true
    ): stdClass {
        // Валидация JWT
        $timestamp = \is_null(static::$timestamp) ? \time() : static::$timestamp;

        if (empty($keyOrKeyArray)) {
            throw new InvalidArgumentException('Ключ не должен быть пустым');
        }
        $tks = \explode('.', $jwt);
        if (\count($tks) !== 3) {
            throw new UnexpectedValueException('Неверное кол-во сегментов');
        }
        list($headb64, $bodyb64, $cryptob64) = $tks;
        $headerRaw = static::urlsafeB64Decode($headb64);
        if (null === ($header = static::jsonDecode($headerRaw))) {
            throw new UnexpectedValueException('Ошибка шифрования заголовка');
        }
        $payloadRaw = static::urlsafeB64Decode($bodyb64);
        if (null === ($payload = static::jsonDecode($payloadRaw))) {
            throw new UnexpectedValueException('Ошибка шифрования параметров');
        }
        if (\is_array($payload)) {
            // Предотвращает фатальную ошибку PHP в крайних случаях, когда полезная нагрузка представляет собой пустой массив
            $payload = (object) $payload;
        }
        if (!$payload instanceof stdClass) {
            throw new UnexpectedValueException('Полезная нагрузка должна быть в формате JSON');
        }
        $sig = static::urlsafeB64Decode($cryptob64);
        if (empty($header->alg)) {
            throw new UnexpectedValueException('Пустой алгоритм');
        }
        if (empty(static::$supported_algs[$header->alg])) {
            throw new UnexpectedValueException('Алгоритм не поддерживается');
        }

        $key = self::getKey($keyOrKeyArray, property_exists($header, 'kid') ? $header->kid : null);

        // Проверка алгоритма
        if (!self::constantTimeEquals($key->getAlgorithm(), $header->alg)) {
            throw new UnexpectedValueException('Некорректный ключ для этого алгоритма');
        }
        if ($header->alg === 'ES256' || $header->alg === 'ES384') {
            // OpenSSL ожидает последовательность DER ASN.1 для подписей ES256/ES384.
            $sig = self::signatureToDER($sig);
        }
        if (!self::verify("{$headb64}.{$bodyb64}", $sig, $key->getKeyMaterial(), $header->alg)) {
            throw new SignatureInvalidException('Ошибка верификации сигнатуры');
        }

        if (empty($timeException) || $timeException == true) {
            // Проверьте nbf, если он определен. 
            // Это время, с которого токен можно использовать.
            if (isset($payload->nbf) && $payload->nbf > ($timestamp + static::$leeway)) {
                throw new BeforeValidException(
                    'Токен невозможно использовать до ' . \date(DateTime::ISO8601, $payload->nbf)
                );
            }

            // Убедитесь, что этот токен был создан до «сейчас». 
            // Это предотвращает использование токенов, 
            // которые были созданы для последующего использования 
            // (и/или неправильно использовали утверждение nbf).
            if (isset($payload->iat) && $payload->iat > ($timestamp + static::$leeway)) {
                throw new BeforeValidException(
                    'Токен невозможно использовать до ' . \date(DateTime::ISO8601, $payload->iat)
                );
            }

            // Проверка срока годности токена
            if (isset($payload->exp) && ($timestamp - static::$leeway) >= $payload->exp) {
                throw new ExpiredException('Срок действия токена истёк');
            }
        }

        return $payload;
    }

    /** Преобразует и подписывает массив PHP в строку JWT
     *
     * @param array<mixed>          $payload массив PHP
     * @param string|resource|OpenSSLAsymmetricKey|OpenSSLCertificate $key Секретный ключ
     * @param string                $alg     Поддерживаемые алгоритмы: 'ES384','ES256', 'HS256', 'HS384',
     *                                       'HS512', 'RS256', 'RS384', and 'RS512'
     * @param string                $keyId
     * @param array<string, string> $head    Массив с элементами заголовка для прикрепления
     *
     * @return string Подписанный JWT
     *
     * @uses jsonEncode
     * @uses urlsafeB64Encode
     */
    public static function encode(
        array $payload,
        $key,
        string $alg,
        string $keyId = null,
        array $head = null
    ): string {
        $header = ['typ' => 'JWT', 'alg' => $alg, 'gen' => 'LOCALZET'];
        if ($keyId !== null) {
            $header['kid'] = $keyId;
        }
        if (isset($head) && \is_array($head)) {
            $header = \array_merge($head, $header);
        }
        $segments = [];
        $segments[] = static::urlsafeB64Encode((string) static::jsonEncode($header));
        $segments[] = static::urlsafeB64Encode((string) static::jsonEncode($payload));
        $signing_input = \implode('.', $segments);

        $signature = static::sign($signing_input, $key, $alg);
        $segments[] = static::urlsafeB64Encode($signature);

        return \implode('.', $segments);
    }

    /** Подпись строки с заданным ключом и алгоритмом.
     *
     * @param string $msg  Сообщение для подписи
     * @param string|resource|OpenSSLAsymmetricKey|OpenSSLCertificate  $key  Секретный ключ
     * @param string $alg  Поддерживаемые алгоритмы: 'ES384','ES256', 'HS256', 'HS384',
     *                    'HS512', 'RS256', 'RS384', and 'RS512'
     *
     * @return string Зашифрованное сообщение
     *
     * @throws DomainException Указан неподдерживаемый алгоритм или неверный ключ
     */
    public static function sign(
        string $msg,
        $key,
        string $alg
    ): string {
        if (empty(static::$supported_algs[$alg])) {
            throw new DomainException('Алгоритм не поддерживается');
        }
        list($function, $algorithm) = static::$supported_algs[$alg];
        switch ($function) {
            case 'hash_hmac':
                if (!\is_string($key)) {
                    throw new InvalidArgumentException('При использовании HMAC ключ должен быть строкой');
                }
                return \hash_hmac($algorithm, $msg, $key, true);
            case 'openssl':
                $signature = '';
                $success = \openssl_sign($msg, $signature, $key, $algorithm); // @phpstan-ignore-line
                if (!$success) {
                    throw new DomainException('OpenSSL не может подписать данные');
                }
                if ($alg === 'ES256') {
                    $signature = self::signatureFromDER($signature, 256);
                } elseif ($alg === 'ES384') {
                    $signature = self::signatureFromDER($signature, 384);
                }
                return $signature;
            case 'sodium_crypto':
                if (!\function_exists('sodium_crypto_sign_detached')) {
                    throw new DomainException('libsodium недоступен');
                }
                if (!\is_string($key)) {
                    throw new InvalidArgumentException('При использовании EdDSA ключ должен быть строкой');
                }
                try {
                    // В качестве ключа используется последняя непустая строка.
                    $lines = array_filter(explode("\n", $key));
                    $key = base64_decode((string) end($lines));
                    return sodium_crypto_sign_detached($msg, $key);
                } catch (Exception $e) {
                    throw new DomainException($e->getMessage(), 0, $e);
                }
        }

        throw new DomainException('Алгоритм не поддерживается');
    }

    /**
     * Проверка сигнатуры с помощью сообщения, ключа и метода. Не все методы симметричны,
     * поэтому у нас должен быть отдельный метод проверки и подписи.
     *
     * @param string $msg         Исходное сообщение (header и body)
     * @param string $signature   Оригинальная сигнатура
     * @param string|resource|OpenSSLAsymmetricKey|OpenSSLCertificate  $keyMaterial Для HS* работает строковый ключ. 
     *                                                                              Для RS* должен быть экземпляр OpenSSLAsymmetricKey
     * @param string $alg         Алгоритм
     *
     * @return bool
     *
     * @throws DomainException Неверный алгоритм, неверный ключ или сбой OpenSSL
     */
    private static function verify(
        string $msg,
        string $signature,
        $keyMaterial,
        string $alg
    ): bool {
        if (empty(static::$supported_algs[$alg])) {
            throw new DomainException('Алгоритм не поддерживается');
        }

        list($function, $algorithm) = static::$supported_algs[$alg];
        switch ($function) {
            case 'openssl':
                $success = \openssl_verify($msg, $signature, $keyMaterial, $algorithm); // @phpstan-ignore-line
                if ($success === 1) {
                    return true;
                }
                if ($success === 0) {
                    return false;
                }
                // Возвращает 1 в случае успеха, 0 в случае неудачи, -1 в случае ошибки.
                throw new DomainException(
                    'Ошибка OpenSSL: ' . \openssl_error_string()
                );
            case 'sodium_crypto':
                if (!\function_exists('sodium_crypto_sign_verify_detached')) {
                    throw new DomainException('libsodium недоступен');
                }
                if (!\is_string($keyMaterial)) {
                    throw new InvalidArgumentException('При использовании EdDSA ключ должен быть строкой');
                }
                try {
                    // В качестве ключа используется последняя непустая строка.
                    $lines = array_filter(explode("\n", $keyMaterial));
                    $key = base64_decode((string) end($lines));
                    return sodium_crypto_sign_verify_detached($signature, $msg, $key);
                } catch (Exception $e) {
                    throw new DomainException($e->getMessage(), 0, $e);
                }
            case 'hash_hmac':
            default:
                if (!\is_string($keyMaterial)) {
                    throw new InvalidArgumentException('При использовании HMAC ключ должен быть строкой');
                }
                $hash = \hash_hmac($algorithm, $msg, $keyMaterial, true);
                return self::constantTimeEquals($hash, $signature);
        }
    }

    /** Декодировать строку JSON в объект PHP.
     *
     * @param string $input Строка JSON
     *
     * @return mixed Расшифрованная строка JSON
     *
     * @throws DomainException Предоставленная строка была недействительным JSON
     */
    public static function jsonDecode(string $input)
    {
        $obj = \json_decode($input, false, 512, JSON_BIGINT_AS_STRING);

        if ($errno = \json_last_error()) {
            self::handleJsonError($errno);
        } elseif ($obj === null && $input !== 'null') {
            throw new DomainException('Нулевой результат с не нулевым вводом (?!)');
        }
        return $obj;
    }

    /** Кодировать массив PHP в строку JSON.
     *
     * @param array<mixed> $input PHP-массив
     *
     * @return string JSON-представление массива PHP
     *
     * @throws DomainException Предоставленный объект не может быть закодирован в действительный JSON
     */
    public static function jsonEncode(array $input): string
    {
        if (PHP_VERSION_ID >= 50400) {
            $json = \json_encode($input, \JSON_UNESCAPED_SLASHES);
        } else {
            // PHP 5.3
            $json = \json_encode($input);
        }
        if ($errno = \json_last_error()) {
            self::handleJsonError($errno);
        } elseif ($json === 'null' && $input !== null) {
            throw new DomainException('Нулевой результат с не нулевым вводом (?!)');
        }
        if ($json === false) {
            throw new DomainException('Объект не может быть представлен в формате JSON');
        }
        return $json;
    }

    /** Декодировать строку из URL-безопасного Base64.
     *
     * @param string $input Строка в кодировке Base64
     *
     * @return string Декодированная строка
     *
     * @throws InvalidArgumentException Недопустимые символы base64
     */
    public static function urlsafeB64Decode(string $input): string
    {
        $remainder = \strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= \str_repeat('=', $padlen);
        }
        return \base64_decode(\strtr($input, '-_', '+/'));
    }

    /** Кодировать строку в URL-безопасный Base64.
     *
     * @param string $input Строка, которую вы хотите закодировать
     *
     * @return string Кодировка base64 того, что вы передали
     */
    public static function urlsafeB64Encode(string $input): string
    {
        return \str_replace('=', '', \strtr(\base64_encode($input), '+/', '-_'));
    }


    /** Определить, предоставлен ли алгоритм для каждого ключа
     *
     * @param Key|ArrayAccess<string,Key>|array<string,Key> $keyOrKeyArray
     * @param string|null            $kid
     *
     * @throws UnexpectedValueException
     *
     * @return Key
     */
    private static function getKey(
        $keyOrKeyArray,
        ?string $kid
    ): Key {
        if ($keyOrKeyArray instanceof Key) {
            return $keyOrKeyArray;
        }

        if (empty($kid)) {
            throw new UnexpectedValueException('"kid" пуст, невозможно найти корректный ключ');
        }

        if ($keyOrKeyArray instanceof CachedKeySet) {
            // Пропустим проверку «isset», так как она автоматически обновится, если не установлена
            return $keyOrKeyArray[$kid];
        }

        if (!isset($keyOrKeyArray[$kid])) {
            throw new UnexpectedValueException('"kid" пуст, невозможно найти корректный ключ');
        }

        return $keyOrKeyArray[$kid];
    }

    /**
     * @param string $left  Строка известной длины для сравнения
     * @param string $right Введенная пользователем строка
     * @return bool
     */
    public static function constantTimeEquals(string $left, string $right): bool
    {
        if (\function_exists('hash_equals')) {
            return \hash_equals($left, $right);
        }
        $len = \min(self::safeStrlen($left), self::safeStrlen($right));

        $status = 0;
        for ($i = 0; $i < $len; $i++) {
            $status |= (\ord($left[$i]) ^ \ord($right[$i]));
        }
        $status |= (self::safeStrlen($left) ^ self::safeStrlen($right));

        return ($status === 0);
    }

    /** Вспомогательный метод для создания ошибки JSON.
     *
     * @param int $errno Номер ошибки из json_last_error()
     *
     * @throws DomainException
     *
     * @return void
     */
    private static function handleJsonError(int $errno): void
    {
        $messages = [
            JSON_ERROR_DEPTH => 'Превышена максимальный объём стека',
            JSON_ERROR_STATE_MISMATCH => 'Некорректный JSON',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX => 'Ошибка синтаксиса, некорректный JSON',
            JSON_ERROR_UTF8 => 'Некорректный UTF-8' //PHP >= 5.3.3
        ];
        throw new DomainException(
            isset($messages[$errno])
                ? $messages[$errno]
                : 'Ошибка JSON: ' . $errno
        );
    }

    /** Получить количество байтов в криптографических строках.
     *
     * @param string $str
     *
     * @return int
     */
    private static function safeStrlen(string $str): int
    {
        if (\function_exists('mb_strlen')) {
            return \mb_strlen($str, '8bit');
        }
        return \strlen($str);
    }

    /** Преобразование подписи ECDSA в последовательность ASN.1 DER
     *
     * @param   string $sig Подпись ECDSA для преобразования
     * @return  string Закодированный объект DER
     */
    private static function signatureToDER(string $sig): string
    {
        // Разделить подпись на r-значение и s-значение
        $length = max(1, (int) (\strlen($sig) / 2));
        list($r, $s) = \str_split($sig, $length);

        // Обрезать ведущие нули
        $r = \ltrim($r, "\x00");
        $s = \ltrim($s, "\x00");

        // Преобразование значений r и s из беззнаковых целых чисел с обратным порядком байтов в
        // знаковое дополнение до двух
        if (\ord($r[0]) > 0x7f) {
            $r = "\x00" . $r;
        }
        if (\ord($s[0]) > 0x7f) {
            $s = "\x00" . $s;
        }

        return self::encodeDER(
            self::ASN1_SEQUENCE,
            self::encodeDER(self::ASN1_INTEGER, $r) .
                self::encodeDER(self::ASN1_INTEGER, $s)
        );
    }

    /** Кодирует значение в объект DER.
     *
     * @param   int     $type Тег DER
     * @param   string  $value Значение для кодирования
     *
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

    /** Кодирует подпись из объекта DER.
     *
     * @param   string  $der Бинарная подпись в формате DER
     * @param   int     $keySize Количество бит в ключе
     *
     * @return  string  Подпись
     */
    private static function signatureFromDER(string $der, int $keySize): string
    {
        // OpenSSL возвращает подписи ECDSA в виде двоичного файла ASN.1 DER SEQUENCE.
        list($offset, $_) = self::readDER($der);
        list($offset, $r) = self::readDER($der, $offset);
        list($offset, $s) = self::readDER($der, $offset);

        // Преобразовать r-значение и s-значение из дополнений со знаком два в беззнаковые
        // целые числа с обратным порядком байтов
        $r = \ltrim($r, "\x00");
        $s = \ltrim($s, "\x00");

        // Заполнить r и s так, чтобы они были равны $keySize битам.
        $r = \str_pad($r, $keySize / 8, "\x00", STR_PAD_LEFT);
        $s = \str_pad($s, $keySize / 8, "\x00", STR_PAD_LEFT);

        return $r . $s;
    }

    /** Считывает двоичные данные, закодированные в формате DER, и декодирует их в один объект.
     *
     * @param string $der Двоичные данные в формате DER
     * @param int $offset Смещение потока данных, содержащего объект для декодирования
     *
     * @return array{int, string|null} Новое смещение и декодированный объект
     */
    private static function readDER(string $der, int $offset = 0): array
    {
        $pos = $offset;
        $size = \strlen($der);
        $constructed = (\ord($der[$pos]) >> 5) & 0x01;
        $type = \ord($der[$pos++]) & 0x1f;

        // Длина
        $len = \ord($der[$pos++]);
        if ($len & 0x80) {
            $n = $len & 0x1f;
            $len = 0;
            while ($n-- && $pos < $size) {
                $len = ($len << 8) | \ord($der[$pos++]);
            }
        }

        // Значение
        if ($type === self::ASN1_BIT_STRING) {
            $pos++; // Пропустить первый октет содержимого (индикатор заполнения)
            $data = \substr($der, $pos, $len - 1);
            $pos += $len - 1;
        } elseif (!$constructed) {
            $data = \substr($der, $pos, $len);
            $pos += $len;
        } else {
            $data = null;
        }

        return [$pos, $data];
    }
}
