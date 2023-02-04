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


class MakeMiddlewareCommand extends Command
{
    protected static $defaultName = 'make:middleware';
    protected static $defaultDescription = 'Создать промежуточное ПО';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Название промежуточного ПО');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $output->writeln("Создание промежуточного класса $name");
        if (!($pos = strrpos($name, '/'))) {
            $name = ucfirst($name);
            $file = "app/middleware/$name.php";
            $namespace = 'app\middleware';
        } else {
            $path = 'app/middleware/' . substr($name, 0, $pos);
            $name = ucfirst(substr($name, $pos + 1));
            $file = "$path/$name.php";
            $namespace = str_replace('/', '\\', $path);
        }
        $this->createMiddleware($name, $namespace, $file);

        return self::SUCCESS;
    }


    /**
     * @param $name
     * @param $namespace
     * @param $path
     * @return void
     */
    protected function createMiddleware($name, $namespace, $file)
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $middleware_content = <<<EOF
<?php
namespace $namespace;

use localzet\FrameX\MiddlewareInterface;
use localzet\FrameX\Http\Response;
use localzet\FrameX\Http\Request;

class $name implements MiddlewareInterface
{
    public function process(Request \$request, callable \$next) : Response
    {
        return \$next(\$request);
    }
    
}

EOF;
        file_put_contents($file, $middleware_content);
    }
}
