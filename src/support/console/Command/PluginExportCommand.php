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
use support\console\Util;

class PluginExportCommand extends Command
{
    protected static $defaultName = 'plugin:export';
    protected static $defaultDescription = 'Экспорт плагина';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addOption('name', 'name', InputOption::VALUE_REQUIRED, 'Название плагина (framex/plugin)');
        $this->addOption('source', 'source', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Папки для экспорта');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Экспорт плагина');
        $name = strtolower($input->getOption('name'));
        if (!strpos($name, '/')) {
            $output->writeln('<error>Некорректное название, оно должно содержать символ \'/\' , например framex/plugin</error>');
            return self::INVALID;
        }
        $namespace = Util::nameToNamespace($name);
        $path_relations = $input->getOption('source');
        if (!in_array("config/plugin/$name", $path_relations)) {
            if (is_dir("config/plugin/$name")) {
                $path_relations[] = "config/plugin/$name";
            }
        }
        $original_dest = $dest = base_path() . "/vendor/$name";
        $dest .= '/src';
        $this->writeInstallFile($namespace, $path_relations, $dest);
        $output->writeln("<info>Создание $dest/Install.php</info>");
        foreach ($path_relations as $source) {
            $base_path = pathinfo("$dest/$source", PATHINFO_DIRNAME);
            if (!is_dir($base_path)) {
                mkdir($base_path, 0777, true);
            }
            $output->writeln("<info>Копирую $source в $dest/$source </info>");
            copy_dir($source, "$dest/$source");
        }
        $output->writeln("<info>Сохраняю $name в $original_dest</info>");
        return self::SUCCESS;
    }

    /**
     * @param $namespace
     * @param $path_relations
     * @param $dest_dir
     * @return void
     */
    protected function writeInstallFile($namespace, $path_relations, $dest_dir)
    {
        if (!is_dir($dest_dir)) {
            mkdir($dest_dir, 0777, true);
        }
        $relations = [];
        foreach ($path_relations as $relation) {
            $relations[$relation] = $relation;
        }
        $relations = var_export($relations, true);
        $install_php_content = <<<EOT
<?php
namespace $namespace;

class Install
{
    const FRAMEX_PLUGIN = true;

    /**
     * @var array
     */
    protected static \$pathRelation = $relations;

    /**
     * @return void
     */
    public static function install()
    {
        static::installByRelation();
    }

    /**
     * @return void
     */
    public static function uninstall()
    {
    }

    /**
     * @return void
     */
    public static function installByRelation()
    {
        foreach (static::\$pathRelation as \$source => \$dest) {
            if (\$pos = strrpos(\$dest, '/')) {
                \$parentDir = base_path() . '/' . substr(\$dest, 0, \$pos);
                if (!is_dir(\$parentDir)) {
                    mkdir(\$parentDir, 0777, true);
                }
            }
            \$sourceFile = __DIR__ . "/\$source";
            copy_dir(\$sourceFile, base_path() . "/\$dest", true);
            echo "Создан \$dest\r\n";
            if (is_file(\$sourceFile)) {
                @unlink(\$sourceFile);
            }
        }
    }

    /**
     * @return void
     */
    public static function uninstallByRelation()
    {
        foreach (static::\$pathRelation as \$source => \$dest) {
            \$path = base_path()."/\$dest";
            if (!is_dir(\$path) && !is_file(\$path)) {
                continue;
            }
            echo "Удаление \$dest\r\n";
            if (is_file(\$path) || is_link(\$path)) {
                @unlink(\$path);
                continue;
            }
            remove_dir(\$path);
        }
    }
    
}
EOT;
        file_put_contents("$dest_dir/Install.php", $install_php_content);
    }
}
