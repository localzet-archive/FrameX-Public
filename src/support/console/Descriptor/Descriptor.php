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
use support\console\Exception\InvalidArgumentException;
use support\console\Input\InputArgument;
use support\console\Input\InputDefinition;
use support\console\Input\InputOption;
use support\console\Output\OutputInterface;

/**
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
abstract class Descriptor implements DescriptorInterface
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * {@inheritdoc}
     */
    public function describe(OutputInterface $output, object $object, array $options = [])
    {
        $this->output = $output;

        switch (true) {
            case $object instanceof InputArgument:
                $this->describeInputArgument($object, $options);
                break;
            case $object instanceof InputOption:
                $this->describeInputOption($object, $options);
                break;
            case $object instanceof InputDefinition:
                $this->describeInputDefinition($object, $options);
                break;
            case $object instanceof Command:
                $this->describeCommand($object, $options);
                break;
            case $object instanceof Application:
                $this->describeApplication($object, $options);
                break;
            default:
                throw new InvalidArgumentException(sprintf('Object of type "%s" is not describable.', get_debug_type($object)));
        }
    }

    /**
     * Writes content to output.
     */
    protected function write(string $content, bool $decorated = false)
    {
        $this->output->write($content, false, $decorated ? OutputInterface::OUTPUT_NORMAL : OutputInterface::OUTPUT_RAW);
    }

    /**
     * Describes an InputArgument instance.
     */
    abstract protected function describeInputArgument(InputArgument $argument, array $options = []);

    /**
     * Describes an InputOption instance.
     */
    abstract protected function describeInputOption(InputOption $option, array $options = []);

    /**
     * Describes an InputDefinition instance.
     */
    abstract protected function describeInputDefinition(InputDefinition $definition, array $options = []);

    /**
     * Describes a Command instance.
     */
    abstract protected function describeCommand(Command $command, array $options = []);

    /**
     * Describes an Application instance.
     */
    abstract protected function describeApplication(Application $application, array $options = []);
}
