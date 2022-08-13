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
     * Update (обновление)
     */

    /**
     * Обновить
     * 
     * @param array $where
     * @param array|InterfaceEntity $data
     * @return bool
     */
    public static function update(array $where, array|InterfaceEntity $data);

    /**
     * Create (создание)
     */

    /**
     * Создать
     * 
     * @param array|InterfaceEntity $data
     * @return bool
     */
    public static function create(array|InterfaceEntity $data);

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

    /**
     * Arrays (массивы)
     */

    /**
     * Массив из сущности
     * 
     * @param InterfaceEntity $entity
     * @param array $params
     * @return array|false
     */
    public static function getArray(InterfaceEntity $entity, array $params = false);

    /**
     * Массив массивов из массива сущностей
     * 
     * @param InterfaceEntity[] $entities
     * @param array $params
     * @return array[]|false
     */
    public static function getArrays(InterfaceEntity $entities, array $params = false);
}
