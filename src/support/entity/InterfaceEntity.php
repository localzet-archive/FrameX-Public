<?php

/**
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

namespace support\entity;

interface InterfaceEntity
{
    /**
     * @param array $raw
     * @return void
     */
    public function __construct(array $raw);

    /**
     * Установка значений
     * @param array $data
     */
    public function set($data);

    /**
     * Получение значений
     * @param string $keys
     */
    public function get(...$keys = null);

    /**
     * Установка значения
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value);

    /**
     * Проверка данных
     * @param string $key
     */
    public function __isset($key);

    /**
     * Удаление данных
     * @param string $key
     */
    public function __unset($key);

    /**
     * Получение данных
     * @param string $key
     */
    public function __get($key);
}
