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

namespace support\relation;

/**
 * Abstract Relation
 */
abstract class abstractRelation
{
    public static ?string $table;

    /**
     * Get (получение)
     */

    /**
     * Получить один
     * 
     * @param array $where
     * @return array|false
     */
    public static function getOne(array $where)
    {
        if (empty($where)) {
            throw new exceptionRelation('Невозможно получить пустую запись', 400);
        } else {
            $return = db();

            foreach ($where as $key => $value) {
                $return->where($key, $value);
            }

            return $return->getOne(static::$table) ?? false;
        }
    }

    /**
     * Получить
     * 
     * @param array $where
     * @return array[]|false
     */
    public static function get(array $where)
    {
        if (empty($where) || empty($value)) {
            return db()->get(static::$table) ?? false;
        } else {
            $return = db();

            foreach ($where as $key => $value) {
                $return->where($key, $value);
            }

            return $return->get(static::$table) ?? false;
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
            throw new exceptionRelation('Невозможно создать пустую запись', 400);
        } else {
            return (bool) db()->insert(static::$table, $data);
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
        if (empty($where) || empty($value)) {
            throw new exceptionRelation('Невозможно удалить пустую запись', 400);
        } else {
            if (!empty(static::get($where))) return true;
            $return = db();

            foreach ($where as $key => $value) {
                $return->where($key, $value);
            }

            return (bool) $return->delete(static::$table) ?? false;
        }
    }
}
