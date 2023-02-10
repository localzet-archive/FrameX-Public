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

class StatusCommand extends Command
{
    protected static $defaultName = 'status';
    protected static $defaultDescription = 'Статус сервера. Используй -d, чтобы показать статус в реальном времени.';

    protected function configure(): void
    {
        $this->addOption('live', 'd', InputOption::VALUE_NONE, 'статус в реальном времени');
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
