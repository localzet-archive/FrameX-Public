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


class MakeCommandCommand extends Command
{
    protected static $defaultName = 'make:command';
    protected static $defaultDescription = 'Добавить команду';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Название команды');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $command = $name = $input->getArgument('name');
        $output->writeln("Создание команды $name");
        if (!($pos = strrpos($name, '/'))) {
            $name = $this->getClassName($name);
            $file = "app/command/$name.php";
            $namespace = 'app\command';
        } else {
            $path = 'app/' . substr($name, 0, $pos) . '/command';
            $name = $this->getClassName(substr($name, $pos + 1));
            $file = "$path/$name.php";
            $namespace = str_replace('/', '\\', $path);
        }
        $this->createCommand($name, $namespace, $file, $command);

        return self::SUCCESS;
    }

    protected function getClassName($name)
    {
        return preg_replace_callback('/:([a-zA-Z])/', function ($matches) {
            return strtoupper($matches[1]);
        }, ucfirst($name)) . 'Command';
    }

    /**
     * @param $name
     * @param $namespace
     * @param $path
     * @return void
     */
    protected function createCommand($name, $namespace, $file, $command)
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $desc = str_replace(':', ' ', $command);
        $command_content = <<<EOF
<?php

namespace $namespace;

use support\console\Command\Command;
use support\console\Input\InputInterface;
use support\console\Input\InputOption;
use support\console\Input\InputArgument;
use support\console\Output\OutputInterface;


class $name extends Command
{
    protected static \$defaultName = '$command';
    protected static \$defaultDescription = '$desc';

    /**
     * @return void
     */
    protected function configure()
    {
        \$this->addArgument('name', InputArgument::OPTIONAL, 'Описание');
    }

    /**
     * @param InputInterface \$input
     * @param OutputInterface \$output
     * @return int
     */
    protected function execute(InputInterface \$input, OutputInterface \$output)
    {
        \$name = \$input->getArgument('name');
        \$output->writeln('Выполнена команда $command');
        return self::SUCCESS;
    }

}

EOF;
        file_put_contents($file, $command_content);
    }
}
