<?php

/**
 * @author    localzet<creator@localzet.ru>
 * @copyright localzet<creator@localzet.ru>
 * @link      https://www.localzet.ru/
 * @license   https://www.localzet.ru/license GNU GPLv3 License
 */

namespace localzet\FrameX\Route;

use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\RouteCollector;
use localzet\FrameX\Route as Router;

/**
 * Class Route
 * @package localzet\FrameX
 */
class Route
{
    /**
     * @var string|null
     */
    protected $_name = null;

    /**
     * @var array
     */
    protected $_methods = [];

    /**
     * @var string
     */
    protected $_path = '';

    /**
     * @var callable
     */
    protected $_callback = null;

    /**
     * @var array
     */
    protected $_middlewares = [];

    /**
     * Route constructor.
     * @param $methods
     * @param $path
     */
    public function __construct($methods, $path, $callback)
    {
        $this->_methods = (array) $methods;
        $this->_path = $path;
        $this->_callback = $callback;
    }

    /**
     * @return mixed|null
     */
    public function getName()
    {
        return $this->_name ?? null;
    }

    /**
     * @param $name
     * @return $this
     */
    public function name($name)
    {
        $this->_name = $name;
        Router::setByName($name, $this);
        return $this;
    }

    /**
     * @param null $middleware
     * @return $this|array
     */
    public function middleware($middleware = null)
    {
        if ($middleware === null) {
            return $this->_middlewares;
        }
        $this->_middlewares = array_merge($this->_middlewares, (array)$middleware);
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        return $this->_methods;
    }

    /**
     * @return callable
     */
    public function getCallback()
    {
        return $this->_callback;
    }

    /**
     * @return array
     */
    public function getMiddleware()
    {
        return $this->_middlewares;
    }

    /**
     * @param $parameters
     * @return string
     */
    public function url($parameters = [])
    {
        if (empty($parameters)) {
            return $this->_path;
        }
        $path = str_replace(['[', ']'], '', $this->_path);
        return preg_replace_callback('/\{(.*?)(?:\:[^\}]*?)*?\}/', function ($matches) use (&$parameters) {
            if (!$parameters) {
                return $matches[0];
            }
            if (isset($parameters[$matches[1]])) {
                $value = $parameters[$matches[1]];
                unset($parameters[$matches[1]]);
                return $value;
            }
            $key = key($parameters);
            if (is_int($key)) {
                $value = $parameters[$key];
                unset($parameters[$key]);
                return $value;
            }
            return $matches[0];
        }, $path);
    }
}
