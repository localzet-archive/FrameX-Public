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


class InstallCommand extends Command
{
    protected static $defaultName = 'install';
    protected static $defaultDescription = 'Запуск устанощика FrameX';

    /**
     * @return void
     */
    protected function configure()
    {
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Выполнить установку Framex");
        $install_function = "\\localzet\\FrameX\\Install::install";
        if (is_callable($install_function)) {
            $install_function();
            return self::SUCCESS;
        }
        $output->writeln('<error>Эта команда требует localzet/framex версии >= 1.0.3</error>');
        return self::FAILURE;
    }
}
