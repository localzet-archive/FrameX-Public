<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace support\console\Descriptor;

use support\console\Application;
use support\console\Command\Command;
use support\console\Helper\Helper;
use support\console\Input\InputArgument;
use support\console\Input\InputDefinition;
use support\console\Input\InputOption;
use support\console\Output\OutputInterface;

/**
 * Markdown descriptor.
 *
 * @author Jean-François Simon <contact@jfsimon.fr>
 *
 * @internal
 */
class MarkdownDescriptor extends Descriptor
{
    /**
     * {@inheritdoc}
     */
    public function describe(OutputInterface $output, object $object, array $options = [])
    {
        $decorated = $output->isDecorated();
        $output->setDecorated(false);

        parent::describe($output, $object, $options);

        $output->setDecorated($decorated);
    }

    /**
     * {@inheritdoc}
     */
    protected function write(string $content, bool $decorated = true)
    {
        parent::write($content, $decorated);
    }

    /**
     * {@inheritdoc}
     */
    protected function describeInputArgument(InputArgument $argument, array $options = [])
    {
        $this->write(
            '#### `'.($argument->getName() ?: '<none>')."`\n\n"
            .($argument->getDescription() ? preg_replace('/\s*[\r\n]\s*/', "\n", $argument->getDescription())."\n\n" : '')
            .'* Обязателен: '.($argument->isRequired() ? 'Да' : 'Нет')."\n"
            .'* Массив: '.($argument->isArray() ? 'Да' : 'Нет')."\n"
            .'* По умолчанию: `'.str_replace("\n", '', var_export($argument->getDefault(), true)).'`'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function describeInputOption(InputOption $option, array $options = [])
    {
        $name = '--'.$option->getName();
        if ($option->isNegatable()) {
            $name .= '|--no-'.$option->getName();
        }
        if ($option->getShortcut()) {
            $name .= '|-'.str_replace('|', '|-', $option->getShortcut()).'';
        }

        $this->write(
            '#### `'.$name.'`'."\n\n"
            .($option->getDescription() ? preg_replace('/\s*[\r\n]\s*/', "\n", $option->getDescription())."\n\n" : '')
            .'* Принимает значения: '.($option->acceptValue() ? 'Да' : 'Нет')."\n"
            .'* Значение обязательно: '.($option->isValueRequired() ? 'Да' : 'Нет')."\n"
            .'* Несколько значений: '.($option->isArray() ? 'Да' : 'Нет')."\n"
            .'* Is negatable: '.($option->isNegatable() ? 'Да' : 'Нет')."\n"
            .'* По умолчанию: `'.str_replace("\n", '', var_export($option->getDefault(), true)).'`'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function describeInputDefinition(InputDefinition $definition, array $options = [])
    {
        if ($showArguments = \count($definition->getArguments()) > 0) {
            $this->write('### Аргументы');
            foreach ($definition->getArguments() as $argument) {
                $this->write("\n\n");
                if (null !== $describeInputArgument = $this->describeInputArgument($argument)) {
                    $this->write($describeInputArgument);
                }
            }
        }

        if (\count($definition->getOptions()) > 0) {
            if ($showArguments) {
                $this->write("\n\n");
            }

            $this->write('### Опции');
            foreach ($definition->getOptions() as $option) {
                $this->write("\n\n");
                if (null !== $describeInputOption = $this->describeInputOption($option)) {
                    $this->write($describeInputOption);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function describeCommand(Command $command, array $options = [])
    {
        if ($options['short'] ?? false) {
            $this->write(
                '`'.$command->getName()."`\n"
                .str_repeat('-', Helper::width($command->getName()) + 2)."\n\n"
                .($command->getDescription() ? $command->getDescription()."\n\n" : '')
                .'### Использование'."\n\n"
                .array_reduce($command->getAliases(), function ($carry, $usage) {
                    return $carry.'* `'.$usage.'`'."\n";
                })
            );

            return;
        }

        $command->mergeApplicationDefinition(false);

        $this->write(
            '`'.$command->getName()."`\n"
            .str_repeat('-', Helper::width($command->getName()) + 2)."\n\n"
            .($command->getDescription() ? $command->getDescription()."\n\n" : '')
            .'### Использование'."\n\n"
            .array_reduce(array_merge([$command->getSynopsis()], $command->getAliases(), $command->getUsages()), function ($carry, $usage) {
                return $carry.'* `'.$usage.'`'."\n";
            })
        );

        if ($help = $command->getProcessedHelp()) {
            $this->write("\n");
            $this->write($help);
        }

        $definition = $command->getDefinition();
        if ($definition->getOptions() || $definition->getArguments()) {
            $this->write("\n\n");
            $this->describeInputDefinition($definition);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function describeApplication(Application $application, array $options = [])
    {
        $describedNamespace = $options['namespace'] ?? null;
        $description = new ApplicationDescription($application, $describedNamespace);
        $title = $this->getApplicationTitle($application);

        $this->write($title."\n".str_repeat('=', Helper::width($title)));

        foreach ($description->getNamespaces() as $namespace) {
            if (ApplicationDescription::GLOBAL_NAMESPACE !== $namespace['id']) {
                $this->write("\n\n");
                $this->write('**'.$namespace['id'].':**');
            }

            $this->write("\n\n");
            $this->write(implode("\n", array_map(function ($commandName) use ($description) {
                return sprintf('* [`%s`](#%s)', $commandName, str_replace(':', '', $description->getCommand($commandName)->getName()));
            }, $namespace['commands'])));
        }

        foreach ($description->getCommands() as $command) {
            $this->write("\n\n");
            if (null !== $describeCommand = $this->describeCommand($command, $options)) {
                $this->write($describeCommand);
            }
        }
    }

    private function getApplicationTitle(Application $application): string
    {
        if ('UNKNOWN' !== $application->getName()) {
            if ('UNKNOWN' !== $application->getVersion()) {
                return sprintf('%s %s', $application->getName(), $application->getVersion());
            }

            return $application->getName();
        }

        return 'Инструмент консоли';
    }
}
