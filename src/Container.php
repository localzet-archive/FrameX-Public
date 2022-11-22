<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io/framex
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.ru>
 * @copyright   Copyright (c) 2018-2022 Localzet Group
 * @license     https://www.localzet.ru/license GNU GPLv3 License
 */

namespace localzet\FrameX;

use Psr\Container\ContainerInterface;
use localzet\FrameX\Exception\NotFoundException;

/**
 * Class Container
 */
class Container implements ContainerInterface
{

    /**
     * @var array
     */
    protected $_instances = [];

    /**
     * @param string $name
     * @return mixed
     * @throws NotFoundException
     */
    public function get(string $name)
    {
        if (!isset($this->_instances[$name])) {
            if (!\class_exists($name)) {
                throw new NotFoundException("Класс '$name' не найден");
            }
            $this->_instances[$name] = new $name();
        }
        return $this->_instances[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return \array_key_exists($name, $this->_instances);
    }

    /**
     * @param string $name
     * @param array $constructor
     * @return mixed
     * @throws NotFoundException
     */
    public function make(string $name, array $constructor = [])
    {
        if (!\class_exists($name)) {
            throw new NotFoundException("Класс '$name' не найден");
        }
        return new $name(...array_values($constructor));
    }
}
