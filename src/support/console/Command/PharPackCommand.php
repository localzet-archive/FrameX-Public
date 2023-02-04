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
use Phar;
use RuntimeException;

class PharPackCommand extends Command
{
    protected static $defaultName = 'phar:pack';
    protected static $defaultDescription = 'Может быть стоит просто упаковать проект в файлы Phar. Легко распространять и использовать.';

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->checkEnv();

        $phar_file_output_dir = base_path();

        if (!file_exists($phar_file_output_dir) && !is_dir($phar_file_output_dir)) {
            if (!mkdir($phar_file_output_dir, 0777, true)) {
                throw new RuntimeException("Не удалось создать выходной каталог phar-файла. Пожалуйста, проверьте разрешение.");
            }
        }

        $phar_filename = 'master.phar';
        if (empty($phar_filename)) {
            throw new RuntimeException('Пожалуйста, установите имя файла phar.');
        }

        $phar_file = rtrim($phar_file_output_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $phar_filename;
        if (file_exists($phar_file)) {
            unlink($phar_file);
        }

        $phar = new Phar($phar_file, 0, 'framex');

        $phar->startBuffering();

        $signature_algorithm = Phar::SHA256;
        $phar->setSignatureAlgorithm($signature_algorithm);

        $phar->buildFromDirectory(BASE_PATH);

        $exclude_files = ['.env', 'LICENSE', 'composer.json', 'composer.lock', 'start.php'];

        foreach ($exclude_files as $file) {
            if ($phar->offsetExists($file)) {
                $phar->delete($file);
            }
        }

        $output->writeln('Сбор файлов завершен, начинаю добавлять файлы в Phar.');

        $phar->setStub("#!/usr/bin/env php
<?php
define('IN_PHAR', true);
Phar::mapPhar('framex');
require 'phar://framex/framex';
__HALT_COMPILER();
");

        $output->writeln('Запись запросов в Phar архив и сохранение изменений');

        $phar->stopBuffering();
        unset($phar);
        return self::SUCCESS;
    }

    /**
     * @throws RuntimeException
     */
    private function checkEnv(): void
    {
        if (!class_exists(Phar::class, false)) {
            throw new RuntimeException("Расширение «Phar» требуется для сборки Phar");
        }

        if (ini_get('phar.readonly')) {
            throw new RuntimeException(
                "'phar.readonly' сейчас в 'On', phar должен установить его в 'Off' или выполнить 'php -d phar.readonly=0 ./framex phar:pack'"
            );
        }
    }
}
