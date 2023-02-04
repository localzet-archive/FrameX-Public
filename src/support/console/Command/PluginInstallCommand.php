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
use support\console\Input\InputArgument;
use support\console\Util;


class PluginInstallCommand extends Command
{
    protected static $defaultName = 'plugin:install';
    protected static $defaultDescription = 'Установить плагин';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Название плагина (framex/plugin)');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $output->writeln("Установка плагина $name");
        $namespace = Util::nameToNamespace($name);
        $install_function = "\\{$namespace}\\Install::install";
        $plugin_const = "\\{$namespace}\\Install::FRAMEX_PLUGIN";
        if (defined($plugin_const) && is_callable($install_function)) {
            $install_function();
        }
        return self::SUCCESS;
    }
}
