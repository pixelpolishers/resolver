<?php

namespace PixelPolishers\Resolver\Command;

use InvalidArgumentException;
use PixelPolishers\Resolver\Generator\VisualStudio\Vs2010\Generator as Vs2010Generator;
use PixelPolishers\Resolver\Generator\VisualStudio\Vs2015\Generator as Vs2015Generator;
use PixelPolishers\Resolver\Utils\FileSystem;
use PixelPolishers\Resolver\Variable\Parser;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Generate extends AbstractCommand
{
    private $generators;

    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->generators = [
            'vs2010' => [
                'name' => 'Visual Studio 2010',
                'fqcn' => Vs2010Generator::class,
            ],
            'vs2015' => [
                'name' => 'Visual Studio 2015',
                'fqcn' => Vs2015Generator::class,
            ],
        ];
    }

    protected function configure()
    {
        $this->setName('generate');
        $this->setDescription('Generates IDE project files for the current project.');
        $this->addArgument('ide', InputArgument::REQUIRED, 'The ide to generate project files for.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ide = $input->getArgument('ide');
        $config = $this->getConfig();

        if (!array_key_exists($ide, $this->generators)) {
            throw new InvalidArgumentException(sprintf('The "%s" generator is not supported.', $ide));
        }

        $output->writeln('Generating project files for ' . $this->generators[$ide]['name']);

        $variableParser = new Parser();
        $variableParser->push('config', $config);
        $variableParser->set('ide.type', $ide);

        $outputDirectory = $variableParser->parse('projects/$(ide.type)/');
        FileSystem::createDirectory($outputDirectory, 0777);

        $generatorFqcn = $this->generators[$ide]['fqcn'];
        $generator = new $generatorFqcn($config);
        $generator->setVariableParser($variableParser);
        $generator->generate($outputDirectory);
    }
}
