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

namespace support\relation;

/**
 * Interface Relation
 */
interface InterfaceRelation
{
    /**
     * Get (получение)
     */

    /**
     * Получить один
     * 
     * @param array $where
     * @return array|false
     */
    public static function getOne(array $where);

    /**
     * Получить
     * 
     * @param array $where
     * @return array[]|false
     */
    public static function get(array $where = null);

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
     * Создать связь
     * 
     * @return bool
     */
    public static function addRelation($id1, $id2);

    /**
     * Удалить связь
     * 
     * @return bool
     */
    public static function delRelation($id1, $id2);
}
