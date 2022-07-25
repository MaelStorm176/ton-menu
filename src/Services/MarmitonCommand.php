<?php

namespace App\Services;

use App\Entity\Ingredient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;


#[AsCommand(name:'marmiton:get-images')]
class MarmitonCommand extends Command
{
    private MarmitonManager $marmitonManager;

    public function __construct(EntityManagerInterface $em,string $name = null)
    {
        parent::__construct($name);
        $this->marmitonManager = new MarmitonManager($em->getRepository(Ingredient::class));
    }

    protected function configure()
    {
        $this
            ->setName('marmiton:get-images')
            ->setDescription('Udpate ingredients image by marmiton images')
            ->setHelp('This command allows you to update ingredients image by marmiton images')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->marmitonManager->updateIngredientsImage();
        $output->writeln('Images updated');
        return Command::SUCCESS;
    }
}