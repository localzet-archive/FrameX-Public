<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io/framex
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.ru>
 * @copyright   Copyright (c) 2018-2022 RootX Group
 * @license     https://www.localzet.ru/license GNU GPLv3 License
 */

namespace support;

/**
 * Простой сборщик данных
 */
final class Collection
{
    /**
     * Данные
     *
     * @var mixed
     */
    protected $collection = null;

    /**
     * @param mixed $data
     */
    public function __construct($data = null)
    {
        $this->collection = new \stdClass();

        if (is_object($data)) {
            $this->collection = $data;
        }

        $this->collection = (object)$data;
    }

    /**
     * Извлекает всю коллекцию в виде массива
     *
     * @return mixed
     */
    public function toArray()
    {
        return (array)$this->collection;
    }

    /**
     * Извлекает элемент
     *
     * @param $property
     *
     * @return mixed
     */
    public function get($property)
    {
        if ($this->exists($property)) {
            return $this->collection->$property;
        }

        return null;
    }

    /**
     * Добавить или обновить элемент
     *
     * @param $property
     * @param mixed $value
     */
    public function set($property, $value)
    {
        if ($property) {
            $this->collection->$property = $value;
        }
    }

    /**
     * @param $property
     *
     * @return Collection
     */
    public function filter($property)
    {
        if ($this->exists($property)) {
            $data = $this->get($property);

            if (!is_a($data, 'Collection')) {
                $data = new Collection($data);
            }

            return $data;
        }

        return new Collection([]);
    }

    /**
     * Проверяет, есть ли элемент в коллекции
     *
     * @param $property
     *
     * @return bool
     */
    public function exists($property)
    {
        return property_exists($this->collection, $property);
    }

    /**
     * Определяет, пуста ли коллекция
     *
     * @return bool
     */
    public function isEmpty()
    {
        return !(bool)$this->count();
    }

    /**
     * Подсчитать все предметы в коллекции
     *
     * @return int
     */
    public function count()
    {
        return count($this->properties());
    }

    /**
     * Возвращает имена всех свойств элементов
     *
     * @return array
     */
    public function properties()
    {
        $properties = [];

        foreach ($this->collection as $key => $value) {
            $properties[] = $key;
        }

        return $properties;
    }

    /**
     * Возвращает все значения элементов
     *
     * @return array
     */
    public function values()
    {
        $values = [];

        foreach ($this->collection as $value) {
            $values[] = $value;
        }

        return $values;
    }
}
