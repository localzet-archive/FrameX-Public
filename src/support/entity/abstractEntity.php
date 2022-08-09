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

namespace support\entity;

/**
 * Abstract Entity
 */
abstract class abstractEntity
{
    /**
     * @param array $raw
     * @return void
     */
    public function __construct(array $raw)
    {
        if (empty($raw)) {
            throw new exceptionEntity("Пустая сущность", 500);
        }

        foreach ($raw as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
