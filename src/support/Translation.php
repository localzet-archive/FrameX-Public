<?php

/**
 * @package     Triangle Engine (FrameX)
 * @link        https://github.com/localzet/FrameX
 * @link        https://github.com/Triangle-org/Engine
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2022 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace support;

use Symfony\Component\Translation\Translator;
use localzet\FrameX\Exception\NotFoundException;
use function basename;
use function config;
use function get_realpath;
use function glob;
use function pathinfo;
use function request;
use function substr;

/**
 * Class Translation
 * @method static string trans(?string $id, array $parameters = [], string $domain = null, string $locale = null)
 * @method static void setLocale(string $locale)
 * @method static string getLocale()
 */
class Translation
{

    /**
     * @var Translator[]
     */
    protected static $instance = [];

    /**
     * @param string $plugin
     * @return Translator
     * @throws NotFoundException
     */
    public static function instance(string $plugin = ''): Translator
    {
        if (!isset(static::$instance[$plugin])) {
            $config = config($plugin ? "plugin.$plugin.translation" : 'translation', []);
            // Phar support. Compatible with the 'realpath' function in the phar file.
            if (!$translationsPath = get_realpath($config['path'])) {
                throw new NotFoundException("File {$config['path']} not found");
            }

            static::$instance[$plugin] = $translator = new Translator($config['locale']);
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
                foreach (glob($translationsPath . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*' . $opts['extension']) as $file) {
                    $domain = basename($file, $opts['extension']);
                    $dirName = pathinfo($file, PATHINFO_DIRNAME);
                    $locale = substr(strrchr($dirName, DIRECTORY_SEPARATOR), 1);
                    if ($domain && $locale) {
                        $translator->addResource($opts['format'], $file, $locale, $domain);
                    }
                }
            }
        }
        return static::$instance[$plugin];
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws NotFoundException
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $request = request();
        $plugin = $request->plugin ?? '';
        return static::instance($plugin)->{$name}(...$arguments);
    }
}
