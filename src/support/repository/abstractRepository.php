<?php

/**
 * @package     T-University Project
 * @link        https://localzet.gitbook.io
 * 
 * @author      localzet <creator@localzet.ru>
 * 
 * @copyright   Copyright (c) 2018-2020 Zorin Projects 
 * @copyright   Copyright (c) 2020-2022 NONA Team
 * 
 * @license     https://www.localzet.ru/license GNU GPLv3 License
 */

namespace support\repository;

use support\entity\InterfaceEntity;

/**
 * Abstract Repository
 */
abstract class abstractRepository
{
    public static ?string $entity = '\support\entity\abstractEntity';
    public static ?string $table;

    /**
     * Get (получение)
     */

    /**
     * Экземпляр сущности из репозитория
     * 
     * @param string|array $where
     * @param mixed $value
     * @return InterfaceEntity
     */
    public static function getOne($where, $value = null)
    {
        if (is_array($where) && (strtoupper($value) == 'AND' || strtoupper($value) == 'OR')) {
            $query = db();
            foreach ($where as $prop => $val) {
                $query->where($prop, $val, '=', strtoupper($value));
            }
            return new static::$entity($query->getOne(static::$table));
        }

        return new static::$entity(db()->where($where, $value)->getOne(static::$table));
    }

    /**
     * Массив сущностей из репозитория
     * 
     * @param string $where
     * @param mixed $value
     * @return InterfaceEntity[]
     */
    public static function get($where = null, $value = null)
    {
        if (empty($where) || empty($value)) {
            $raw = db()->get(static::$table);
        } else {
            $raw = db()->where($where, $value)->get(static::$table);
        }

        return static::getEntities($raw);
    }

    /**
     * Сущность по ID
     * 
     * @param string|int $id
     * @return InterfaceEntity
     */
    public static function getById($id)
    {
        if (empty($id)) {
            throw new exceptionRepository('Пустой id', 400);
        }
        return static::getOne('id', $id);
    }

    /**
     * Update (обновление)
     */

    /**
     * Обновить
     * 
     * @param array $data
     * @return bool
     */
    public static function update(array $data)
    {
        if (empty($data)) {
            throw new exceptionRepository('Невозможно создать пустую запись', 400);
        }

        return (bool) db()->where('id', $data['id'])->update(static::$table, $data);
    }

    /**
     * Create (создание)
     */

    /**
     * Создать
     * 
     * @param array $data
     * @return bool
     */
    public static function create(array $data)
    {
        if (empty($data)) {
            throw new exceptionRepository('Невозможно создать пустую запись', 400);
        }

        return (bool) db()->insert(static::$table, $data);
    }

    /**
     * Delete (удаление)
     */

    /**
     * Удалить
     * 
     * @param string|int $id
     * @return bool
     */
    public static function delete($id)
    {
        if (empty($id)) {
            throw new exceptionRepository('Пустой id', 400);
        }
        return (bool) db()->where('id', $id)->delete(static::$table);
    }

    /**
     * Entity (сущности)
     */

    /**
     * Сущность из массива
     * 
     * @param array $data
     * @return InterfaceEntity
     */
    public static function getEntity(array $data)
    {
        if (empty($data)) {
            throw new exceptionRepository('Пустые данные', 400);
        }
        return new static::$entity($data);
    }

    /**
     * Массив сущностей из массива
     * 
     * @param array[] $data
     * @return InterfaceEntity[]
     */
    public static function getEntities(array $data)
    {
        if (empty($data)) {
            throw new exceptionRepository('Пустые данные', 400);
        }

        $entities = array();
        foreach ($data as $one) {
            $entities[] = static::getEntity($one);
        }

        return $entities;
    }
}
