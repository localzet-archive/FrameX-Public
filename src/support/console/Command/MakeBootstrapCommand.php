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


class MakeBootstrapCommand extends Command
{
    protected static $defaultName = 'make:bootstrap';
    protected static $defaultDescription = 'Добавить класс в автозагрузку';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Название класса для автозагрузки');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $output->writeln("Создание загрузчика $name");
        if (!($pos = strrpos($name, '/'))) {
            $name = ucfirst($name);
            $file = "app/bootstrap/$name.php";
            $namespace = 'app\bootstrap';
        } else {
            $path = 'app/' . substr($name, 0, $pos) . '/bootstrap';
            $name = ucfirst(substr($name, $pos + 1));
            $file = "$path/$name.php";
            $namespace = str_replace('/', '\\', $path);
        }
        $this->createBootstrap($name, $namespace, $file);
        //$this->addConfig("$namespace\\$name", config_path() . '/bootstrap.php');

        return self::SUCCESS;
    }

    /**
     * @param $name
     * @param $namespace
     * @param $file
     * @return void
     */
    protected function createBootstrap($name, $namespace, $file)
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $bootstrap_content = <<<EOF
<?php

namespace $namespace;

use localzet\FrameX\Bootstrap;

class $name implements Bootstrap
{
    public static function start(\$server)
    {
        // Это консоль?
        \$is_console = !\$server;
        if (\$is_console) {
            // Если вы не хотите выполнять это в консоли, просто оставь return.
            return;
        }


    }

}

EOF;
        file_put_contents($file, $bootstrap_content);
    }

    public function addConfig($class, $config_file)
    {
        $config = include $config_file;
        if (!in_array($class, $config ?? [])) {
            $config_file_content = file_get_contents($config_file);
            $config_file_content = preg_replace('/\];/', "    $class::class,\n];", $config_file_content);
            file_put_contents($config_file, $config_file_content);
        }
    }
}
