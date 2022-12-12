<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io/framex
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.ru>
 * @copyright   Copyright (c) 2018-2022 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace support\relation;

/**
 * Interface Relation
 */
interface InterfaceRelation
{
    /**
     * Создать
     * 
     * @param array $data Данные в виде массива
     * @return bool
     */
    public static function create(array $data): bool;

    /**
     * Получить
     * 
     * @param array $where Массив условий ['field' => 'value']
     * @param array $params Дополнительные свойства
     * @param bool $multi true = get(), false = getOne()
     * @param array $func Дополнительная обработка функцией из \support\database\MySQL
     * @param string $operator Оператор условий ('=', 'LIKE')
     * @param string $cond Для нескольких условий (OR, AND)
     * @param int|null $numRows Лимит [$offset, $count] или $count
     * @param string $columns Выборка столбцов
     * @return array
     */
    public static function get(
        array $where = [],
        array $params = [],
        bool $multi = true,

        // where
        array $func = [],
        string $operator = '=',
        string $cond = 'AND',

        // get/getOne
        int|null $numRows = null, // Лимит ($offset, $count)
        string $columns = '*',
    );

    /**
     * Обновить
     * 
     * @param array $input Массив массивов условий и данных ['where' => ['field' => 'value'], 'data' => [key => value, ...]]
     * @param array $func Дополнительная обработка функцией из \support\database\MySQL
     * @param string $operator Оператор условий ('=', 'LIKE')
     * @param string $cond Для нескольких условий (OR, AND)
     * @return bool
     */
    public static function update(
        array $input,
        bool $multi = false,

        // where
        array $func = [],
        string $operator = '=',
        string $cond = 'AND',
    ): bool;

    /**
     * Удалить
     * 
     * @param array $input Массив массивов условий [['field' => 'value']]
     * @param array $func Дополнительная обработка функцией из \support\database\MySQL
     * @param string $operator Оператор условий ('=', 'LIKE')
     * @param string $cond Для нескольких условий (OR, AND)
     * @return bool
     */
    public static function delete(
        array $input,
        bool $multi = false,

        // where
        array $func = [],
        string $operator = '=',
        string $cond = 'AND',
    ): bool;
}
