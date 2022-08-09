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
     * Получить экземпляр сущности из репозитория
     * 
     * @param string|array $where
     * @param mixed $value
     * @return InterfaceEntity
     */
    public static function getOne($where, $value = null);

    /**
     * Получить массив сущностей из репозитория
     * 
     * @param string $where
     * @param mixed $value
     * @return InterfaceEntity[]
     */
    public static function get($where = null, $value = null);

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
     * @param array $data
     * @return bool
     */
    public static function update(array $data);

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
     * @param string|int $id
     * @return bool
     */
    public static function delete($id);

    /**
     * Entity (сущности)
     */

    /**
     * Сущность из массива
     * 
     * @param array $data
     * @return InterfaceEntity
     */
    public static function getEntity(array $data);

    /**
     * Массив сущностей из массива
     * 
     * @param array[] $data
     * @return InterfaceEntity[]
     */
    public static function getEntities(array $data);
}
