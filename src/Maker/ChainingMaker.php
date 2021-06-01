<?php

/*
 * This file is part of the Symfony MakerBundle package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\MakerBundle\Maker;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\MakerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class ChainingMaker implements MakerInterface
{
    private $makers;
    private $makersToRun = [];

    public function __construct(iterable $makers)
    {
        $this->makers = $makers;
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        foreach ($this->makers as $maker) {
            $maker->configureCommand($command, $inputConfig);
        }
    }

    public function configureDependencies(DependencyBuilder $dependencies)
    {
        foreach ($this->makers as $maker) {
            $maker->configureDependencies($dependencies);
        }
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        foreach ($this->makers as $maker) {
            if (!$io->confirm('Do you want to run "'.$maker::getCommandName().'" ('.lcfirst($maker::getCommandDescription()).')?')) {
                continue;
            }

            $this->makersToRun[] = $maker;

            $maker->interact($input, $io, $command);
        }
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        foreach ($this->makersToRun as $maker) {
            $maker->generate($input, $io, $generator);
        }
    }

    public static function getCommandName(): string
    {
        throw new \LogicException(sprintf('"%s" must be used with the "command_name" tag attribute.', __CLASS__));
    }
}
