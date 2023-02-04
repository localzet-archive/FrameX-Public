<?php

/**
 * @package     Triangle Engine (FrameX)
 * @link        https://github.com/localzet/FrameX
 * @link        https://github.com/Triangle-org/Engine
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2022 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace localzet\FrameX\Http;

use localzet\FrameX\Route\Route;
use function current;
use function filter_var;
use function ip2long;
use function is_array;
use function strpos;
use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_NO_PRIV_RANGE;
use const FILTER_FLAG_NO_RES_RANGE;
use const FILTER_VALIDATE_IP;

/**
 * Class Request
 */
class Request extends \localzet\Core\Protocols\Http\Request
{
    /**
     * @var string
     */
    public $plugin = null;

    /**
     * @var string
     */
    public $app = null;

    /**
     * @var string
     */
    public $controller = null;

    /**
     * @var string
     */
    public $action = null;

    /**
     * @var Route
     */
    public $route = null;

    /**
     * @return mixed|null
     */
    public function all()
    {
        return $this->post() + $this->get();
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed|null
     */
    public function input(string $name, $default = null)
    {
        $post = $this->post();
        if (isset($post[$name])) {
            return $post[$name];
        }
        $get = $this->get();
        return $get[$name] ?? $default;
    }

    /**
     * @param array $keys
     * @return array
     */
    public function only(array $keys): array
    {
        $all = $this->all();
        $result = [];
        foreach ($keys as $key) {
            if (isset($all[$key])) {
                $result[$key] = $all[$key];
            }
        }
        return $result;
    }

    /**
     * @param array $keys
     * @return mixed|null
     */
    public function except(array $keys)
    {
        $all = $this->all();
        foreach ($keys as $key) {
            unset($all[$key]);
        }
        return $all;
    }

    /**
     * @param string|null $name
     * @return null|UploadFile[]|UploadFile
     */
    public function file($name = null)
    {
        $files = parent::file($name);
        if (null === $files) {
            return $name === null ? [] : null;
        }
        if ($name !== null) {
            // Multi files
            if (is_array(current($files))) {
                return $this->parseFiles($files);
            }
            return $this->parseFile($files);
        }
        $uploadFiles = [];
        foreach ($files as $name => $file) {
            // Multi files
            if (is_array(current($file))) {
                $uploadFiles[$name] = $this->parseFiles($file);
            } else {
                $uploadFiles[$name] = $this->parseFile($file);
            }
        }
        return $uploadFiles;
    }

    /**
     * @param array $file
     * @return UploadFile
     */
    protected function parseFile(array $file): UploadFile
    {
        return new UploadFile($file['tmp_name'], $file['name'], $file['type'], $file['error']);
    }

    /**
     * @param array $files
     * @return array
     */
    protected function parseFiles(array $files): array
    {
        $uploadFiles = [];
        foreach ($files as $key => $file) {
            if (is_array(current($file))) {
                $uploadFiles[$key] = $this->parseFiles($file);
            } else {
                $uploadFiles[$key] = $this->parseFile($file);
            }
        }
        return $uploadFiles;
    }

    /**
     * @return string
     */
    public function getRemoteIp(): string
    {
        return $this->connection->getRemoteIp();
    }

    /**
     * @return int
     */
    public function getRemotePort(): int
    {
        return $this->connection->getRemotePort();
    }

    /**
     * @return string
     */
    public function getLocalIp(): string
    {
        return $this->connection->getLocalIp();
    }

    /**
     * @return int
     */
    public function getLocalPort(): int
    {
        return $this->connection->getLocalPort();
    }

    /**
     * @param bool $safeMode
     * @return string
     */
    public function getRealIp(bool $safeMode = true): string
    {
        $remoteIp = $this->getRemoteIp();
        if ($safeMode && !static::isIntranetIp($remoteIp)) {
            return $remoteIp;
        }
        return $this->header('x-real-ip', $this->header(
            'x-forwarded-for',
            $this->header('client-ip', $this->header(
                'x-client-ip',
                $this->header('via', $remoteIp)
            ))
        ));
    }

    /**
     * @return string
     */
    public function url(): string
    {
        return '//' . $this->host() . $this->path();
    }

    /**
     * @return string
     */
    public function fullUrl(): string
    {
        return '//' . $this->host() . $this->uri();
    }

    /**
     * @return bool
     */
    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * @return bool
     */
    public function isPjax(): bool
    {
        return (bool)$this->header('X-PJAX');
    }

    /**
     * @return bool
     */
    public function expectsJson(): bool
    {
        return ($this->isAjax() && !$this->isPjax()) || $this->acceptJson() || strtoupper($this->method()) != 'GET';
    }

    /**
     * @return bool
     */
    public function acceptJson(): bool
    {
        return false !== strpos($this->header('accept', ''), 'json');
    }

    /**
     * @param string $ip
     * @return bool
     */
    public static function isIntranetIp(string $ip): bool
    {
        // Не IP.
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }
        // Точно ip Интранета? Для IPv4 FALSE может быть не точным, поэтому нам нужно проверить его вручную ниже.
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return true;
        }
        // Ручная проверка IPv4 .
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        // Ручная проверка
        // $reservedIps = [
        //     '167772160'  => 184549375,  // 10.0.0.0 -  10.255.255.255
        //     '3232235520' => 3232301055, // 192.168.0.0 - 192.168.255.255
        //     '2130706432' => 2147483647, // 127.0.0.0 - 127.255.255.255
        //     '2886729728' => 2887778303, // 172.16.0.0 -  172.31.255.255
        // ];
        $reservedIps = [
            1681915904 => 1686110207,   // 100.64.0.0 -  100.127.255.255
            3221225472 => 3221225727,   // 192.0.0.0 - 192.0.0.255
            3221225984 => 3221226239,   // 192.0.2.0 - 192.0.2.255
            3227017984 => 3227018239,   // 192.88.99.0 - 192.88.99.255
            3323068416 => 3323199487,   // 198.18.0.0 - 198.19.255.255
            3325256704 => 3325256959,   // 198.51.100.0 - 198.51.100.255
            3405803776 => 3405804031,   // 203.0.113.0 - 203.0.113.255
            3758096384 => 4026531839,   // 224.0.0.0 - 239.255.255.255
        ];

        $ipLong = ip2long($ip);

        foreach ($reservedIps as $ipStart => $ipEnd) {
            if (($ipLong >= $ipStart) && ($ipLong <= $ipEnd)) {
                return true;
            }
        }
        return false;
    }
}
