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
use support\console\Input\InputOption;
use support\console\Input\InputArgument;
use support\console\Util;


class MakeModelCommand extends Command
{
    protected static $defaultName = 'make:model';
    protected static $defaultDescription = 'Создать модель';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Название модели');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $class = $input->getArgument('name');
        $class = Util::nameToClass($class);
        $output->writeln("Создание модели $class");
        if (!($pos = strrpos($class, '/'))) {
            $file = "app/model/$class.php";
            $namespace = 'app\model';
        } else {
            $path = 'app/' . substr($class, 0, $pos) . '/model';
            $class = ucfirst(substr($class, $pos + 1));
            $file = "$path/$class.php";
            $namespace = str_replace('/', '\\', $path);
        }
        $this->createModel($class, $namespace, $file);

        return self::SUCCESS;
    }

    /**
     * @param $class
     * @param $namespace
     * @param $file
     * @return void
     */
    protected function createModel($class, $namespace, $file)
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $table = Util::classToName($class);
        $table_val = 'null';
        $pk = 'id';
        try {
            if (db()->get("{$table}s")) {
                $table = "{$table}s";
            } else if (db()->get($table)) {
                $table_val = "'$table'";
                $table = "{$table}";
            }
            foreach (db()->orderBy('id', 'desc')->get($table) as $item) {
                if ($item->Key === 'PRI') {
                    $pk = $item->Field;
                    break;
                }
            }
        } catch (\Throwable $e) {
        }
        $model_content = <<<EOF
<?php

namespace $namespace;

use support\Model;

class $class extends Model
{
    /**
     * Таблица, связанная с моделью.
     *
     * @var string
     */
    protected \$table = $table_val;

    /**
     * Первичный ключ, связанный с таблицей.
     *
     * @var string
     */
    protected \$primaryKey = '$pk';

    /**
     * Указывает, должна ли модель быть временной меткой.
     *
     * @var bool
     */
    public \$timestamps = false;
    
    
}

EOF;
        file_put_contents($file, $model_content);
    }
}
