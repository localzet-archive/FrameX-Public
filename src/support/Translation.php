<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io/framex
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.ru>
 * @copyright   Copyright (c) 2018-2022 RootX Group
 * @license     https://www.localzet.ru/license GNU GPLv3 License
 */

namespace support;

use Symfony\Component\Translation\Translator;
use localzet\FrameX\Exception\NotFoundException;

/**
 * Class Translation
 * @package support
 * @method static string trans(?string $id, array $parameters = [], string $domain = null, string $locale = null)
 * @method static void setLocale(string $locale)
 * @method static string getLocale()
 */
class Translation
{

    /**
     * @var Translator[]
     */
    protected static $_instance = [];

    /**
     * @return Translator
     * @throws NotFoundException
     */
    public static function instance(string $plugin = '')
    {
        if (!isset(static::$_instance[$plugin])) {
            $config = \config($plugin ? "plugin.$plugin.translation" : 'translation', []);
            // Phar support. Compatible with the 'realpath' function in the phar file.
            if (!$translations_path = \get_realpath($config['path'])) {
                throw new NotFoundException("File {$config['path']} not found");
            }

            static::$_instance[$plugin] = $translator = new Translator($config['locale']);
            $translator->setFallbackLocales($config['fallback_locale']);

            $classes = [
                'Symfony\Component\Translation\Loader\PhpFileLoader' => [
                    'extension' => '.php',
                    'format' => 'phpfile'
                ],
                'Symfony\Component\Translation\Loader\PoFileLoader' => [
                    'extension' => '.po',
                    'format' => 'pofile'
                ]
            ];

            foreach ($classes as $class => $opts) {
                $translator->addLoader($opts['format'], new $class);
                foreach (\glob($translations_path . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*' . $opts['extension']) as $file) {
                    $domain = \basename($file, $opts['extension']);
                    $dir_name = \pathinfo($file, PATHINFO_DIRNAME);
                    $locale = \substr(strrchr($dir_name, DIRECTORY_SEPARATOR), 1);
                    if ($domain && $locale) {
                        $translator->addResource($opts['format'], $file, $locale, $domain);
                    }
                }
            }
        }
        return static::$_instance[$plugin];
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws NotFoundException
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $request = \request();
        $plugin = $request->plugin ?? '';
        return static::instance($plugin)->{$name}(...$arguments);
    }
}
