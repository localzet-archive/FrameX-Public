<?php

/**
 * @package     FrameX (FX) CLI Plugin
 * @link        https://localzet.gitbook.io
 * 
 * @author      localzet <creator@localzet.ru>
 * 
 * @copyright   Copyright (c) 2018-2020 Zorin Projects 
 * @copyright   Copyright (c) 2020-2022 NONA Team
 * 
 * @license     https://www.localzet.ru/license GNU GPLv3 License
 */

namespace support\console\Command;

use support\console\Command\Command;
use support\console\Input\InputInterface;
use support\console\Output\OutputInterface;

class VersionCommand extends Command
{
    protected static $defaultName = 'version';
    protected static $defaultDescription = 'Показать версии Triangle';

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $installed_file = base_path() . '/vendor/composer/installed.php';
        if (is_file($installed_file)) {
            $version_info = include $installed_file;
        }

        $old = ['localzet/core', 'localzet/framex', 'localzet/webkit'];
        $new = ['triangle/server', 'triangle/engine', 'triangle/framework'];
        foreach ($old + $new as $package) {
            $out = '';
            if (isset($version_info['versions'][$package])) {
                if (in_array($package, $old)) {
                    $output->writeln('Пакет Triangle v1');
                    switch ($package) {
                        case 'localzet/core':
                            $out .= 'WebCore Server';
                            break;
                        case 'localzet/framex':
                            $out .= 'FrameX Engine';
                            break;
                        case 'localzet/webkit':
                            $out .= 'WebKit';
                            break;
                    }
                }

                if (in_array($package, $new)) {
                    $output->writeln('Пакет Triangle v2');
                    switch ($package) {
                        case 'triangle/server':
                            $out .= 'Server';
                            break;
                        case 'triangle/engine':
                            $out .= 'Engine';
                            break;
                        case 'triangle/framework':
                            $out .= 'Framework';
                            break;
                    }
                }

                $out .= ': ' . $version_info['versions'][$package]['pretty_version'];
                $output->writeln("$out");
            }
        }

        return self::SUCCESS;
    }
}
