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


class PluginEnableCommand extends Command
{
    protected static $defaultName = 'plugin:enable';
    protected static $defaultDescription = 'Включить плагин';

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
        $output->writeln("Включение плагина $name");
        if (!strpos($name, '/')) {
            $output->writeln('<error>Некорректное название, оно должно содержать символ \'/\' , например framex/plugin</error>');
            return self::FAILURE;
        }
        $config_file = config_path() . "/plugin/$name/app.php";
        if (!is_file($config_file)) {
            $output->writeln("<error>$config_file не найден</error>");
            return self::FAILURE;
        }
        $config = include $config_file;
        if (!isset($config['enable'])) {
            $output->writeln("<error>Параметр 'enable' не найден</error>");
            return self::FAILURE;
        }
        if ($config['enable']) {
            return self::SUCCESS;
        }
        $config_content = file_get_contents($config_file);
        $config_content = preg_replace('/(\'enable\' *?=> *?)(false)/', '$1true', $config_content);
        file_put_contents($config_file, $config_content);
        return self::SUCCESS;
    }
}
