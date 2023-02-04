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

namespace support\view;

use Throwable;
use localzet\FrameX\View;
use function app_path;
use function array_merge;
use function base_path;
use function config;
use function extract;
use function is_array;
use function ob_end_clean;
use function ob_get_clean;
use function ob_start;
use function request;

/**
 * FrameX Raw: PHP Templating engine
 */
class Raw implements View
{
    /**
     * @var array
     */
    protected static $vars = [];

    /**
     * @param string|array $name
     * @param mixed $value
     */
    public static function assign($name, $value = null)
    {
        static::$vars = array_merge(static::$vars, is_array($name) ? $name : [$name => $value]);
    }

    public static function vars()
    {
        return static::$vars;
    }

    /**
     * @param string $template
     * @param array $vars
     * @param string|null $app
     * @return false|string
     */
    public static function render(string $template, array $vars, string $app = null, $plugin = null): string
    {
        $request = request();
        $plugin = $plugin === null ? ($request->plugin ?? ''): $plugin;
        $configPrefix = $plugin ? "plugin.$plugin." : '';
        $view_global = config("{$configPrefix}view.options.view_global", false);
        $viewSuffix = config("{$configPrefix}view.options.view_suffix", 'html');
        $view_head = config("{$configPrefix}view.options.view_head", "base");
        $view_footer = config("{$configPrefix}view.options.view_footer", "footer");
        $app = $app === null ? $request->app : $app;
        $baseViewPath = $plugin ? base_path() . "/plugin/$plugin/app" : app_path();
        $__template_body__ = $app === '' ? "$baseViewPath/view/$template.$viewSuffix" : "$baseViewPath/$app/view/$template.$viewSuffix";
        $__template_head__ = ($view_global == true ? \app_path() : $baseViewPath) . ($app === '' ? "/view/$view_head.$viewSuffix" : "/$app/view/$view_head.$viewSuffix");
        $__template_foot__ = ($view_global == true ? \app_path() : $baseViewPath) . ($app === '' ? "/view/$view_footer.$viewSuffix" : "/$app/view/$view_footer.$viewSuffix");

        $name = config('app.info.name', 'Triangle App');
        $description = config('app.info.description', 'Simple web application on Triangle Engine');
        $keywords = config('app.info.keywords', 'Triangle, Localzet Group, PHP');
        $viewport = config('app.info.viewport', 'width=device-width, initial-scale=1');

        $domain = config('app.domain', 'https://' . $request->host(true));
        $canonical = config('app.canonical', $request->url());
        $src = config('app.src', 'https://static.localzet.com');
        $fonts = config('app.fonts', 'https://fonts.localzet.com');

        $logo = config('app.info.logo', 'https://static.localzet.com/localzet.svg');
        $og_image = config('app.info.og_image', 'https://static.localzet.com/localzet.svg');

        $owner = config('app.info.owner', 'Ivan Zorin (localzet) <creator@localzet.com>');
        $designer = config('app.info.designer', 'Ivan Zorin (localzet) <creator@localzet.com>');
        $author = config('app.info.author', 'Ivan Zorin (localzet) <creator@localzet.com>');
        $copyright = config('app.info.copyright', 'Localzet Group');
        $reply_to = config('app.info.reply_to', 'support@localzet.com');

        // Backend
        $_API = $_CORE = 'https://core.localzet.com';
        $_STATIC = 'https://static.localzet.com';

        // Frontend
        $_COM = 'https://www.localzet.com';
        $_ru = 'https://www.localzet.ru';

        $custom = [];
        $assets = [];
        $user = [];
        $page = '';
        
        $AppInfo = [
            'name' => $name,
            'description' => $description,
            'keywords' => $keywords,
            'viewport' => $viewport,

            'logo' => $logo,
            'og_image' => $og_image,

            'owner' => $owner,
            'designer' => $designer,
            'author' => $author,
            'copyright' => $copyright,
            'reply_to' => $reply_to,

            'domain' => $domain,
            'canonical' => $canonical,
            'src' => $src,
            'fonts' => $fonts,
        ];

        extract(static::$vars);
        extract($vars);
        ob_start();

        try {
            if (file_exists($__template_head__)) include $__template_head__;
            include $__template_body__;
            if (file_exists($__template_foot__)) include $__template_foot__;
        } catch (Throwable $e) {
            static::$vars = [];
            ob_end_clean();
            throw $e;
        }

        static::$vars = [];
        return ob_get_clean();
    }

    /**
     * @param string $template error/success
     * @param array $vars
     * @return false|string
     */
    public static function renderSys(string $template, array $vars)
    {
        $request = request();
        $plugin = $request->plugin ?? '';
        $config_prefix = $plugin ? "plugin.$plugin." : '';
        $view = \config("{$config_prefix}view.system.$template", \app_path() . "/view/response/$template.phtml");

        $name = config('app.info.name', 'Triangle App');
        $description = config('app.info.description', 'Simple web application on Triangle Engine');
        $keywords = config('app.info.keywords', 'Triangle, Localzet Group, PHP');
        $viewport = config('app.info.viewport', 'width=device-width, initial-scale=1');

        // $domain = config('app.domain', 'https://' . request()->host(true));
        // $canonical = config('app.canonical', request()->url());
        $src = config('app.src', 'https://static.localzet.com');
        $fonts = config('app.fonts', 'https://fonts.localzet.com');

        $logo = config('app.info.logo', 'https://static.localzet.com/localzet.svg');
        $og_image = config('app.info.og_image', 'https://static.localzet.com/localzet.svg');

        $owner = config('app.info.owner', 'Ivan Zorin (localzet) <creator@localzet.com>');
        $designer = config('app.info.designer', 'Ivan Zorin (localzet) <creator@localzet.com>');
        $author = config('app.info.author', 'Ivan Zorin (localzet) <creator@localzet.com>');
        $copyright = config('app.info.copyright', 'Localzet Group');
        $reply_to = config('app.info.reply_to', 'support@localzet.com');

        // Backend
        $_API = $_CORE = 'https://core.localzet.com';
        $_STATIC = 'https://static.localzet.com';

        // Frontend
        $_COM = 'https://www.localzet.com';
        $_ru = 'https://www.localzet.ru';

        $custom = [];
        $assets = [];
        $user = [];
        $page = '';
        
        $AppInfo = [
            'name' => $name,
            'description' => $description,
            'keywords' => $keywords,
            'viewport' => $viewport,

            'logo' => $logo,
            'og_image' => $og_image,

            'owner' => $owner,
            'designer' => $designer,
            'author' => $author,
            'copyright' => $copyright,
            'reply_to' => $reply_to,

            // 'domain' => $domain,
            // 'canonical' => $canonical,
            'src' => $src,
            'fonts' => $fonts,
        ];

        extract(static::$vars);
        extract($vars);
        ob_start();

        try {
            include $view;
        } catch (Throwable $e) {
            static::$vars = [];
            ob_end_clean();
            throw $e;
        }

        static::$vars = [];
        return ob_get_clean();
    }
}
