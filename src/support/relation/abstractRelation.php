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
 * Abstract Relation
 */
abstract class abstractRelation implements InterfaceRelation
{
    public static ?string $table;

    /**
     * Создать
     * 
     * @param array $data Данные в виде массива
     * @return bool
     */
    public static function create(array $data): bool
    {
        if (empty($data)) {
            throw new exceptionRelation('Невозможно создать пустую запись', 400);
        } else {
            return (bool) db()->insert(static::$table, $data);
        }
    }

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
    ) {
        if ($multi == false && empty($where)) throw new exceptionRelation("Недостаточно данных", 400);

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
                    throw new exceptionRelation("Метод $method не существует в " . $db::class, 400);
                }
            }
        }

        if ($multi == true) {
            $result = $db->get(static::$table, $numRows, $columns);
        } else {
            $result = $db->getOne(static::$table, $columns);
        }

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
                $results[] = $item;
            }

            return $results;
        }

        return $result;
    }

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

        // where
        array $func = [],
        string $operator = '=',
        string $cond = 'AND',
    ): bool {
        if (empty($input)) {
            throw new exceptionRelation('Невозможно обновить пустую запись', 400);
        } else {
            $return = true;
            foreach ($input as $one) {
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
                            throw new exceptionRelation("Метод $method не существует в " . $db::class, 400);
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
    public static function delete(
        array $input,

        // where
        array $func = [],
        string $operator = '=',
        string $cond = 'AND',
    ): bool {
        if (empty($input)) {
            throw new exceptionRelation('Невозможно удалить пустую запись', 400);
        } else {
            $return = true;
            foreach ($input as $where) {

                if (empty(static::get($where))) return true;
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
                            throw new exceptionRelation("Метод $method не существует в " . $db::class, 400);
                        }
                    }
                }

                $return = (bool) $db->delete(static::$table);

                if ($return == false) return $return;
            }

            return true;
        }
    }
}
