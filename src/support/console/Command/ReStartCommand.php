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

use support\App;
use support\console\Command\Command;
use support\console\Input\InputInterface;
use support\console\Output\OutputInterface;
use support\console\Input\InputOption;

class ReStartCommand extends Command
{
    protected static $defaultName = 'restart';
    protected static $defaultDescription = 'Перезапустить сервер. Используй -d для запуска в режиме демона. Используй -g, чтобы изящно остановиться.';

    protected function configure(): void
    {
        $this
            ->addOption('daemon', 'd', InputOption::VALUE_NONE, 'DAEMON mode')
            ->addOption('graceful', 'g', InputOption::VALUE_NONE, 'graceful stop');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        App::run();
        return self::SUCCESS;
    }
}
