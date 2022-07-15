<?php

namespace App\Services;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name:'marmiton:get-recipes')]
class MarmitonCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('marmiton:get-recipes')
            ->setDescription('Get recipes from marmiton')
            ->setHelp('This command allows you to get recipes from marmiton')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Hello World!');
        return Command::SUCCESS;
    }
}