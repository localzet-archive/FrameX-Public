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

interface InterfaceRepository
{
    /**
     * Экземпляр сущности из репозитория
     * 
     * @param string|array $where
     * @param mixed $value
     * @param ?string $table
     * @return static::$entity
     */
    public static function getOne($where, $value = null);

    /**
     * Массив сущностей из репозитория
     * 
     * @param string $where
     * @param mixed $value
     * @param ?string $table
     * @return static::$entity[]
     */
    public static function get($where = null, $value = null);

    /**
     * Массив сущностей из массива
     * 
     * @param array $raw
     * @param ?class $entity
     * @return $entity[]
     */
    public static function getEntities($raw, $entity = null);

    /**
     * Сущность по ID
     * 
     * @param string|int $id
     * @return static::$entity
     */
    public static function getById($id);
}
