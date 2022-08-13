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

/**
 * Abstract Entity
 */
abstract class abstractEntity
{
    /**
     * @param array $raw
     * @return void
     */
    public function __construct(array $raw)
    {
        if (empty($raw)) {
            throw new exceptionEntity("Пустая сущность", 500);
        }

        $this->set($raw);
    }

    /**
     * Установка значений
     * @param array $data
     */
    public function set($data)
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Получение значений
     * @param string $keys
     */
    public function get(...$keys = null)
    {
        if (empty($keys)) {
            return (array) clone $this;
        }

        $array = [];

        foreach ($keys as $key) {
            $array[] = $this->{$key};
        }

        return (array) clone $array;
    }

    /**
     * Установка значения
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        return $this->{$key} = $value;
    }

    /**
     * Проверка данных
     * @param string $key
     */
    public function __isset($key)
    {
        return null !== $this->{$key};
    }

    /**
     * Удаление данных
     * @param string $key
     */
    public function __unset($key)
    {
        return $this->{$key} = null;
    }

    /**
     * Получение данных
     * @param string $key
     */
    public function __get($key)
    {
        return $this->{$key};
    }
}
