<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace support\console\Command;

use support\console\Completion\CompletionInput;
use support\console\Completion\CompletionSuggestions;
use support\console\Descriptor\ApplicationDescription;
use support\console\Helper\DescriptorHelper;
use support\console\Input\InputArgument;
use support\console\Input\InputInterface;
use support\console\Input\InputOption;
use support\console\Output\OutputInterface;

/**
 * HelpCommand displays the help for a given command.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class HelpCommand extends Command
{
    private $command;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->ignoreValidationErrors();

        $this
            ->setName('help')
            ->setDefinition([
                new InputArgument('command_name', InputArgument::OPTIONAL, 'Название команды', 'help'),
                new InputOption('format', null, InputOption::VALUE_REQUIRED, 'Формат вывода (txt, xml, json, or md)', 'txt'),
                new InputOption('raw', null, InputOption::VALUE_NONE, 'Черновой вывод справки'),
            ])
            ->setDescription('Отображает справку о командах')
            ->setHelp(<<<'EOF'
Команда <info>%command.name%</info> отображает справку о любой команде:

    "<info>%command.full_name% list</info>" - справка о команде "list"

Вы можете получить вывод в разных форматах, используя опцию <comment>--format</comment>:

    <info>%command.full_name% --format=xml list</info>

Для отображения списка доступных команд используйте команду "<info>list</info>".
EOF
            )
        ;
    }

    public function setCommand(Command $command)
    {
        $this->command = $command;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (null === $this->command) {
            $this->command = $this->getApplication()->find($input->getArgument('command_name'));
        }

        $helper = new DescriptorHelper();
        $helper->describe($output, $this->command, [
            'format' => $input->getOption('format'),
            'raw_text' => $input->getOption('raw'),
        ]);

        $this->command = null;

        return 0;
    }

    public function complete(CompletionInput $input, CompletionSuggestions $suggestions): void
    {
        if ($input->mustSuggestArgumentValuesFor('command_name')) {
            $descriptor = new ApplicationDescription($this->getApplication());
            $suggestions->suggestValues(array_keys($descriptor->getCommands()));

            return;
        }

        if ($input->mustSuggestOptionValuesFor('format')) {
            $helper = new DescriptorHelper();
            $suggestions->suggestValues($helper->getFormats());
        }
    }
}
