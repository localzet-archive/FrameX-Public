<?php

/**
 * @version     1.0.0-dev
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

namespace localzet\FrameX;

use Psr\Container\ContainerInterface;
use localzet\FrameX\Exception\NotFoundException;

/**
 * Class Container
 * @package localzet\FrameX
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
    public function get($name)
    {
        if (!isset($this->_instances[$name])) {
            if (!class_exists($name)) {
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
    public function has($name): bool
    {
        return \array_key_exists($name, $this->_instances);
    }

    /**
     * @param $name
     * @param array $constructor
     * @return mixed
     * @throws NotFoundException
     */
    public function make($name, array $constructor = [])
    {
        if (!class_exists($name)) {
            throw new NotFoundException("Класс '$name' не найден");
        }
        return new $name(...array_values($constructor));
    }
}
