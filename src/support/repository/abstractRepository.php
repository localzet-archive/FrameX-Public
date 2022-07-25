<?php

/**
 * @version     1.0.0-dev
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

/**
 * Abstract Repository
 */
abstract class abstractRepository
{
    public static ?string $entity = '\app\entity\Entity';
    public static ?string $table;

    /**
     * Экземпляр сущности из репозитория
     * 
     * @param string|array $where
     * @param mixed $value
     * @param ?string $table
     * @return static::$entity
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
     * @param ?string $table
     * @return static::$entity[]
     */
    public static function get($where = null, $value = null)
    {
        if (empty($where) || empty($value)) {
            $raw = db()->get(static::$table);
        } else {
            $raw = db()->where($where, $value)->get(static::$table);
        }

        $entities = static::getEntities($raw);

        // $lastGet = [
        //     'raw' => $raw,
        //     'entities' => $entities
        // ];

        // return empty($full) ? $entities : $lastGet;

        return $entities;
    }

    /**
     * Массив сущностей из массива
     * 
     * @param array $raw
     * @param ?class $entity
     * @return $entity[]
     */
    public static function getEntities($raw, $entity = null)
    {
        $entity = !empty($entity) ? $entity : static::$entity;

        $entities = array();
        foreach ($raw as $one) {
            $entities[] = new $entity($one);
        }

        return $entities;
    }

    /**
     * Сущность по ID
     * 
     * @param string|int $id
     * @return static::$entity
     */
    public static function getById($id)
    {
        return static::getOne('id', $id);
    }
}
