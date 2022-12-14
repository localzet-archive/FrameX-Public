<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io/framex
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.ru>
 * @copyright   Copyright (c) 2018-2022 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace support\view;

use localzet\FrameX\View;

/**
 * FrameX Raw: PHP Templating engine
 */
class Raw implements View
{
    /**
     * @var array
     */
    protected static $_vars = [];

    /**
     * @param string|array $name
     * @param mixed $value
     */
    public static function assign($name, $value = null)
    {
        static::$_vars = \array_merge(static::$_vars, \is_array($name) ? $name : [$name => $value]);
    }

    public static function vars()
    {
        return static::$_vars;
    }

    /**
     * @param string $template
     * @param array $vars
     * @param string|null $app
     * @return false|string
     */
    public static function render(string $template, array $vars, string $app = null, $plugin = null)
    {
        $request = \request();
        $plugin = $plugin === null ? $request->plugin : $plugin;
        $app = $app === null ? $request->app : $app;
        $config_prefix = $plugin ? "plugin.$plugin." : '';
        $view_global = \config("{$config_prefix}view.options.view_global", false);
        $view_suffix = \config("{$config_prefix}view.options.view_suffix", 'html');
        $view_head = \config("{$config_prefix}view.options.view_head", "base");
        $view_footer = \config("{$config_prefix}view.options.view_footer", "footer");
        $base_view_path = $plugin ? \base_path() . "/plugin/$plugin/app" : \app_path();
        $__template_body__ = $app === '' ? "$base_view_path/view/$template.$view_suffix" : "$base_view_path/$app/view/$template.$view_suffix";
        $__template_head__ = ($view_global == true ? \app_path() : $base_view_path) . ($app === '' ? "/view/$view_head.$view_suffix" : "/$app/view/$view_head.$view_suffix");
        $__template_foot__ = ($view_global == true ? \app_path() : $base_view_path) . ($app === '' ? "/view/$view_footer.$view_suffix" : "/$app/view/$view_footer.$view_suffix");

        $name = config('app.info.name', 'FrameX App');
        $description = config('app.info.description', 'Simple web application on WebCore Server and FrameX Engine');
        $keywords = config('app.info.keywords', 'FrameX, WebCore, RootX, localzet, Rust, PHP');
        $viewport = config('app.info.viewport', 'width=device-width, initial-scale=1');

        $domain = config('app.domain', 'https://' . $request->host(true));
        $canonical = config('app.canonical', $request->url());
        $src = config('app.src', 'https://src.rootx.ru');
        $fonts = config('app.fonts', 'https://src.rootx.ru/fonts');

        $logo = config('app.info.logo', 'https://src.rootx.ru/localzet.svg');
        $og_image = config('app.info.og_image', 'https://src.rootx.ru/localzet.svg');

        $owner = config('app.info.owner', 'Ivan Zorin (localzet) <creator@localzet.ru>');
        $designer = config('app.info.designer', 'Ivan Zorin (localzet) <creator@localzet.ru>');
        $author = config('app.info.author', 'Ivan Zorin (localzet) <creator@localzet.ru>');
        $copyright = config('app.info.copyright', 'Localzet Group');
        $reply_to = config('app.info.reply_to', 'support@localzet.com');
        
        $_SRC = 'https://src.rootx.ru';
        $_ROOTX = 'https://www.rootx.ru';
        $_LOCALZET = 'https://www.localzet.com';

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

        \extract(static::$_vars);
        \extract($vars);
        \ob_start();

        try {
            if (file_exists($__template_head__)) include $__template_head__;
            include $__template_body__;
            if (file_exists($__template_foot__)) include $__template_foot__;
        } catch (\Throwable $e) {
            static::$_vars = [];
            \ob_end_clean();
            throw $e;
        }

        static::$_vars = [];
        return \ob_get_clean();
    }

    /**
     * @param string $template error/success
     * @param array $vars
     * @return false|string
     */
    public static function renderSys(string $template, array $vars)
    {
        $request = \request();
        $plugin = $request->plugin ?? '';
        $config_prefix = $plugin ? "plugin.$plugin." : '';
        $view = \config("{$config_prefix}view.system.$template", \app_path() . "/view/response/$template.phtml");

        $name = config('app.info.name', 'FrameX App');
        $description = config('app.info.description', 'Simple web application on WebCore Server and FrameX Engine');
        $keywords = config('app.info.keywords', 'FrameX, WebCore, RootX, localzet, Rust, PHP');
        $viewport = config('app.info.viewport', 'width=device-width, initial-scale=1');

        // $domain = config('app.domain', 'https://' . request()->host(true));
        // $canonical = config('app.canonical', request()->url());
        $src = config('app.src', 'https://src.rootx.ru');
        $fonts = config('app.fonts', 'https://src.rootx.ru/fonts');

        $logo = config('app.info.logo', 'https://src.rootx.ru/localzet.svg');
        $og_image = config('app.info.og_image', 'https://src.rootx.ru/localzet.svg');

        $owner = config('app.info.owner', 'Ivan Zorin (localzet) <creator@localzet.ru>');
        $designer = config('app.info.designer', 'Ivan Zorin (localzet) <creator@localzet.ru>');
        $author = config('app.info.author', 'Ivan Zorin (localzet) <creator@localzet.ru>');
        $copyright = config('app.info.copyright', 'Localzet Group');
        $reply_to = config('app.info.reply_to', 'support@localzet.com');
        
        $_SRC = 'https://src.rootx.ru';
        $_ROOTX = 'https://www.rootx.ru';
        $_LOCALZET = 'https://www.localzet.com';

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

        \extract(static::$_vars);
        \extract($vars);
        \ob_start();

        try {
            include $view;
        } catch (\Throwable $e) {
            static::$_vars = [];
            \ob_end_clean();
            throw $e;
        }

        static::$_vars = [];
        return \ob_get_clean();
    }
}
