<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io/framex
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.ru>
 * @copyright   Copyright (c) 2018-2022 Localzet Group
 * @license     https://www.localzet.ru/license GNU GPLv3 License
 */

namespace support\repository;

use support\entity\InterfaceEntity;

/**
 * Abstract Repository
 */
abstract class abstractRepository implements InterfaceRepository
{
    public static ?string $entity = '\support\entity\abstractEntity';
    public static ?string $table = 'Entities';

    /** @var array<string|InterfaceRelation> $relations  */
    public static ?array $relations = [
        'test' => '\support\relation\abstractRelation'
    ];

    /**
     * Создать реляцию
     * 
     * @param string $type Ключ класса реляции из static::$relations
     * @param array $data Данные в виде массива
     * @return bool
     */
    public static function createRelation(string $type, array $data)
    {
        if (empty($type) || empty($data)) {
            throw new exceptionRepository('Невозможно создать пустую запись', 400);
        }

        if (!in_array($type, static::$relations) || !class_exists(static::$relations[$type])) {
            throw new exceptionRepository('Неизвестный класс реляции', 400);
        }

        /** @var InterfaceRelation $relation  */
        $relation = static::$relations[$type];

        return $relation::create($data);
    }

    /**
     * Получить реляцию
     * 
     * @param string $type Ключ класса реляции из static::$relations
     * @param array $where Массив условий ['field' => 'value']
     * @param array $params Дополнительные свойства
     * @param bool $multi true = get(), false = getOne()
     * @param array $func Дополнительная обработка функцией из \support\database\MySQL
     * @param string $operator Оператор условий ('=', 'LIKE')
     * @param string $cond Для нескольких условий (OR, AND)
     * @param int|null $numRows Лимит [$offset, $count] или $count
     * @param string $columns Выборка столбцов
     * @return bool
     */
    public static function getRelation(
        string $type,
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
    ) {
        if (empty($type)) {
            throw new exceptionRepository('Невозможно создать пустую запись', 400);
        }

        if (!in_array($type, static::$relations) || !class_exists(static::$relations[$type])) {
            throw new exceptionRepository('Неизвестный класс реляции', 400);
        }

        /** @var InterfaceRelation $relation  */
        $relation = static::$relations[$type];

        return $relation::get(
            $where,
            $params,
            $multi,
            $func,
            $operator,
            $cond,
            $numRows,
            $columns
        );
    }

    /**
     * Обновить реляцию
     * 
     * @param string $type Ключ класса реляции из static::$relations
     * @param array $input Массив массивов условий и данных ['where' => ['field' => 'value'], 'data' => [key => value, ...]]
     * @param array $func Дополнительная обработка функцией из \support\database\MySQL
     * @param string $operator Оператор условий ('=', 'LIKE')
     * @param string $cond Для нескольких условий (OR, AND)
     * @return bool
     */
    public static function updateRelation(
        string $type,
        array $input,

        // where
        array $func = [],
        string $operator = '=',
        string $cond = 'AND',
    ) {
        if (empty($type) || empty($input)) {
            throw new exceptionRepository('Невозможно создать пустую запись', 400);
        }

        if (!in_array($type, static::$relations) || !class_exists(static::$relations[$type])) {
            throw new exceptionRepository('Неизвестный класс реляции', 400);
        }

        /** @var InterfaceRelation $relation  */
        $relation = static::$relations[$type];

        return $relation::update(
            $input,
            $func,
            $operator,
            $cond,
        );
    }

    /**
     * Обновить реляцию
     * 
     * @param string $type Ключ класса реляции из static::$relations
     * @param array $input Массив массивов условий [['field' => 'value']]
     * @param array $func Дополнительная обработка функцией из \support\database\MySQL
     * @param string $operator Оператор условий ('=', 'LIKE')
     * @param string $cond Для нескольких условий (OR, AND)
     * @return bool
     */
    public static function deleteRelation(
        string $type,
        array $input,

        // where
        array $func = [],
        string $operator = '=',
        string $cond = 'AND',
    ) {
        if (empty($type) || empty($input)) {
            throw new exceptionRepository('Невозможно создать пустую запись', 400);
        }

        if (!in_array($type, static::$relations) || !class_exists(static::$relations[$type])) {
            throw new exceptionRepository('Неизвестный класс реляции', 400);
        }

        /** @var InterfaceRelation $relation  */
        $relation = static::$relations[$type];

        return $relation::delete(
            $input,
            $func,
            $operator,
            $cond,
        );
    }

    /**
     * Сущности
     */

    /**
     * Создать
     * 
     * @param array|InterfaceEntity $data Данные в виде массива или сущности
     * @return bool
     */
    public static function createEntity(
        array|InterfaceEntity $data,
    ): bool {
        if (empty($data)) {
            throw new exceptionRepository('Невозможно создать пустую запись', 400);
        } else {
            // Перевод в массив
            if ($data instanceof InterfaceEntity) {
                $data = $data->get();
            }

            return (bool) db()->insert(static::$table, $data);
        }
    }

    /**
     * Получить
     * 
     * @param array $where Массив условий ['field' => 'value']
     * @param array $params Дополнительные свойства к сущности
     * @param bool $entity Упаковывать в сущности?
     * @param bool $multi true = get(), false = getOne()
     * @param array $func Дополнительная обработка функцией из \support\database\MySQL
     * @param string $operator Оператор условий ('=', 'LIKE')
     * @param string $cond Для нескольких условий (OR, AND)
     * @param int|null $numRows Лимит [$offset, $count] или $count
     * @param string $columns Выборка столбцов
     * @return InterfaceEntity|array|false
     */
    public static function getEntity(
        array $where = [],
        array $params = [],
        bool $entity = true,
        bool $multi = true,

        // where
        array $func = [],
        string $operator = '=',
        string $cond = 'AND',

        // get/getOne
        int|null $numRows = null, // Лимит ($offset, $count)
        string $columns = '*',
    ) {
        if ($multi == false && empty($where)) throw new exceptionRepository("Недостаточно данных", 400);

        // Используем один экземпляр БД
        $db = db()::getInstance();

        // Проверка условий
        if (!empty($where)) {
            foreach ($where as $key => $value) {
                $db->where($key, $value, $operator, $cond);
            }
        }

        // Дополнительные функции (сортировка и т.п.)
        if (!empty($func)) {
            foreach ($func as $method => $args) {
                if (is_string($method) && is_array($args) && method_exists($db, $method)) {
                    call_user_func_array([$db, $method], $args);
                } else {
                    throw new exceptionRepository("Метод $method не существует в " . $db::class, 400);
                }
            }
        }

        if ($multi == true) {
            $result = $db->get(static::$table, $numRows, $columns);
        } else {
            $result = $db->getOne(static::$table, $columns);
        }

        if ($entity == true) {
            return static::ArrayToEntity($result, $params);
        } else {
            if ($params && !empty($params)) {
                $results = [];

                if ($multi == false) {
                    $result = [$result];
                }

                // [
                //     'field' => 'value',
                //     'field' => [
                //         // Функция без вывода (обработчик существующего параметра)
                //         'type' => 'procedure',
                //         'handler' => 'sort',
                //     ],
                //     'field' => [
                //         // Функция с выводом (обработчик value)
                //         'type' => 'wrapper',
                //         'handler' => 'md5',
                //         'value' => 'value'
                //     ],
                //     'field' => [
                //         // Произвольная функция обработки
                //         'type' => 'handler',
                //         'handler' => function (&$item, &$key, &$value) {
                //             $item[$key] = $value['value'];
                //         },
                //         'value' => 'value'
                //     ],
                //     'field' => [
                //         // Исключить значение из массива
                //         'type' => 'unset',
                //     ],
                // ];

                foreach ($result as $item) {
                    foreach ($params as $key => $value) {
                        if (is_array($value)) {
                            if (isset($value['type']) && isset($value['handler']) && (is_callable($value['handler']) || function_exists($value['handler']))) {
                                if ($value['type'] == 'procedure') {
                                    $value['handler']($item[$key]);
                                } else if ($value['type'] == 'wrapper') {
                                    $item[$key] = $value['handler']($value['value']);
                                } else if ($value['type'] == 'handler') {
                                    $value['handler']($item, $key, $value);
                                }
                            } else if (isset($value['type']) && $value['type'] == 'unset') {
                                unset($item[$key]);
                            } else {
                                throw new \Exception("Функция " . $value['name'] . "() не существует");
                            }
                        } else {
                            $item[$key] = $value;
                        }
                    }
                    if ($multi == false) {
                        $results = $item;
                    } else {
                        $results[] = $item;
                    }
                }

                return $results;
            }

            return $result;
        }
    }

    /**
     * Обновить
     * 
     * @param array $input Массив массивов условий и данных ['where' => ['field' => 'value'], 'data' => [key => value, ...]]
     * @param bool $multi true = несколько, false = один
     * @param array $func Дополнительная обработка функцией из \support\database\MySQL
     * @param string $operator Оператор условий ('=', 'LIKE')
     * @param string $cond Для нескольких условий (OR, AND)
     * @return bool
     */
    public static function updateEntity(
        array $input,
        bool $multi = false,

        // where
        array $func = [],
        string $operator = '=',
        string $cond = 'AND',
    ): bool {
        if (empty($input)) {
            throw new exceptionRepository('Невозможно обновить пустую запись', 400);
        } else {
            if ($multi == false) {
                $input = [$input];
            }
            $return = true;
            foreach ($input as $one) {
                if ($one['data'] instanceof InterfaceEntity) {
                    $one['data'] = $one['data']->get();
                }

                $db = db()::getInstance();

                // Проверка условий
                foreach ($one['where'] as $key => $value) {
                    $db->where($key, $value, $operator, $cond);
                }

                // Дополнительные функции (сортировка и т.п.)
                if (!empty($func)) {
                    foreach ($func as $method => $args) {
                        if (is_string($method) && is_array($args) && method_exists($db, $method)) {
                            call_user_func_array([$db, $method], $args);
                        } else {
                            throw new exceptionRepository("Метод $method не существует в " . $db::class, 400);
                        }
                    }
                }

                $return = (bool) $db->update(static::$table, $one['data']);

                if ($return == false) return $return;
            }

            return true;
        }
    }

    /**
     * Удалить
     * 
     * @param array $input Массив массивов условий [['field' => 'value']]
     * @param array $func Дополнительная обработка функцией из \support\database\MySQL
     * @param string $operator Оператор условий ('=', 'LIKE')
     * @param string $cond Для нескольких условий (OR, AND)
     * @return bool
     */
    public static function deleteEntity(
        array $input,
        bool $multi = false,

        // where
        array $func = [],
        string $operator = '=',
        string $cond = 'AND',
    ): bool {
        if (empty($input)) {
            throw new exceptionRepository('Невозможно удалить пустую запись', 400);
        } else {
            if ($multi == false) {
                $input = [$input];
            }
            $return = true;
            foreach ($input as $where) {

                if (empty(static::getEntity($where, entity: false))) return true;
                $db = db()::getInstance();

                // Проверка условий
                foreach ($where as $key => $value) {
                    $db->where($key, $value, $operator, $cond);
                }

                // Дополнительные функции (сортировка и т.п.)
                if (!empty($func)) {
                    foreach ($func as $method => $args) {
                        if (is_string($method) && is_array($args) && method_exists($db, $method)) {
                            call_user_func_array([$db, $method], $args);
                        } else {
                            throw new exceptionRepository("Метод $method не существует в " . $db::class, 400);
                        }
                    }
                }

                $return = (bool) $db->delete(static::$table);

                if ($return == false) return $return;
            }

            return true;
        }
    }



    /**
     * Сущность из массива
     * 
     * @param array $data
     * @param array $params
     * @param array $params Дополнительные свойства к сущности
     * @return InterfaceEntity[]|InterfaceEntity|false
     */
    public static function ArrayToEntity($data = null, array $params = [])
    {
        if (empty($data)) {
            return null;
            // throw new exceptionRepository('Пустые данные', 400);
        } else {
            // Одна или несколько сущностей?
            $multi = false;
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $multi = true;
                    break;
                }
            }

            if ($multi == false) $data = [$data];

            // Обработка
            $entities = [];
            foreach ($data as $one) {

                $entity = new static::$entity($one);

                if ($params && !empty($params)) {
                    foreach ($params as $key => $value) {
                        $entity->{$key} = $value;
                    }
                }

                $entities[] = $entity;
            }

            if ($multi == false) $entities = $entities[0];

            return $entities;
        }
    }

    /**
     * Массив из сущности
     * 
     * @param InterfaceEntity|array $entity
     * @param array $params Дополнительные свойства к массиву
     * @return array|false
     */
    public static function EntityToArray(InterfaceEntity|array $data, array $params = [])
    {
        if (empty($data)) {
            throw new exceptionRepository('Пустые данные', 400);
        } else {
            // Одна или несколько сущностей?
            $multi = is_array($data);

            if ($multi == false) $data = [$data];

            // Обработка
            $arrays = [];
            foreach ($data as $one) {

                $array = $one->get();

                if ($params && !empty($params)) {
                    foreach ($params as $key => $value) {
                        $array[$key] = $value;
                    }
                }

                $arrays[] = $array;
            }

            if ($multi == false) $arrays = $arrays[0];

            return $arrays;
        }
    }
}
