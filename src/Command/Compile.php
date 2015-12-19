<?php

namespace PixelPolishers\Resolver\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Compile extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('compile');
        $this->setDescription('Compiles the project and its dependencies.');
        $this->addOption('projects', 'p', InputOption::VALUE_NONE, 'Compiles the projects themselves.');
        $this->addOption('dependencies', 'd', InputOption::VALUE_NONE, 'Compiles the dependencies.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shouldCompileDependencies = $input->getOption('dependencies');
        $shouldCompileProjects = $input->getOption('projects');

        if (!$shouldCompileDependencies && !$shouldCompileProjects) {
            $shouldCompileDependencies = true;
            $shouldCompileProjects = true;
        }
    }
}
