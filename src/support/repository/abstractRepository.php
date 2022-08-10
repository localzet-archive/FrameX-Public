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
     * Получить один
     * 
     * @param array $where
     * @param string $operator
     * @param string $cond OR, AND
     * @return InterfaceEntity|false
     */
    public static function getOne(array $where, string $operator = '=', string $cond = 'AND', array $params = false)
    {
        if (empty($where)) {
            throw new exceptionRepository('Невозможно получить пустую запись', 400);
        } else {
            $return = db();

            foreach ($where as $key => $value) {
                $return->where($key, $value, $operator, $cond);
            }

            return static::getEntity($return->getOne(static::$table), $params) ?? false;
        }
    }

    /**
     * Получить
     * 
     * @param array $where
     * @param string $operator
     * @param string $cond OR, AND
     * @return InterfaceEntity[]|false
     */
    public static function get(array $where, string $operator = '=', string $cond = 'AND', array $params = false)
    {
        if (empty($where)) {
            return static::getEntities(db()->get(static::$table)) ?? false;
        } else {
            $return = db();

            foreach ($where as $key => $value) {
                $return->where($key, $value, $operator, $cond);
            }

            return static::getEntities($return->get(static::$table), $params) ?? false;
        }
    }

    /**
     * Update (обновление)
     */

    /**
     * Обновить
     * 
     * @param array $where
     * @param array $data
     * @return bool
     */
    public static function update(array $where, array $data)
    {
        if (empty($data) || empty($where)) {
            throw new exceptionRepository('Невозможно обновить пустую запись', 400);
        } else {
            $return = db();

            foreach ($where as $key => $value) {
                $return->where($key, $value);
            }
            return (bool) $return->update(static::$table, $data) ?? false;
        }
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
        } else {
            return (bool) db()->insert(static::$table, $data) ?? false;
        }
    }

    /**
     * Delete (удаление)
     */

    /**
     * Удалить
     * 
     * @param array $where
     * @return bool
     */
    public static function delete(array $where)
    {
        if (empty($where)) {
            throw new exceptionRepository('Пустой id', 400);
        } else {
            if (!empty(static::get($where))) return true;
            $return = db();

            foreach ($where as $key => $value) {
                $return->where($key, $value);
            }

            return (bool) $return->delete(static::$table) ?? false;
        }
    }

    /**
     * Entity (сущности)
     */

    /**
     * Сущность из массива
     * 
     * @param array $data
     * @param array $params
     * @return InterfaceEntity|false
     */
    public static function getEntity(array $data, array $params = false)
    {
        if (empty($data)) {
            throw new exceptionRepository('Пустые данные', 400);
        } else {
            $result = new static::$entity($data);

            if ($params && !empty($params)) {
                foreach ($params as $key => $value) {
                    $result->{$key} = $value;
                }
            }

            return $result;
        }
    }

    /**
     * Массив сущностей из массива
     * 
     * @param array[] $data
     * @param array $params
     * @return InterfaceEntity[]|false
     */
    public static function getEntities(array $data, array $params = false)
    {
        if (empty($data)) {
            throw new exceptionRepository('Пустые данные', 400);
        } else {
            $entities = array();

            foreach ($data as $one) {
                $entities[] = static::getEntity($one, $params);
            }

            return $entities;
        }
    }
}
