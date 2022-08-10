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

interface InterfaceRepository
{
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
    public static function getOne(array $where, string $operator = '=', string $cond = 'AND', array $params = false);

    /**
     * Получить
     * 
     * @param array $where
     * @param string $operator
     * @param string $cond OR, AND
     * @return InterfaceEntity[]|false
     */
    public static function get(array $where, string $operator = '=', string $cond = 'AND', array $params = false);

    /**
     * Получить сущность по ID
     * 
     * @param string|int $id
     * @return InterfaceEntity
     */
    public static function getById($id);

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
    public static function update(array $where, array $data);

    /**
     * Create (создание)
     */

    /**
     * Создать
     * 
     * @param array $data
     * @return bool
     */
    public static function create(array $data);

    /**
     * Delete (удаление)
     */

    /**
     * Удалить
     * 
     * @param array $where
     * @return bool
     */
    public static function delete(array $where);

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
    public static function getEntity(array $data, array $params = false);

    /**
     * Массив сущностей из массива
     * 
     * @param array[] $data
     * @param array $params
     * @return InterfaceEntity[]|false
     */
    public static function getEntities(array $data, array $params = false);
}
