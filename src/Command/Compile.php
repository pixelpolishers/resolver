<?php

namespace PixelPolishers\Resolver\Command;

use PixelPolishers\Resolver\Compiler\CompilerInterface;
use PixelPolishers\Resolver\Compiler\Dependency\DependencyGraph;
use PixelPolishers\Resolver\Compiler\Locator\Msvc;
use PixelPolishers\Resolver\Variable\Parser;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
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
        $this->addArgument('compiler', InputArgument::OPTIONAL, 'The compiler used to compile projects.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shouldCompileDependencies = $input->getOption('dependencies');
        $shouldCompileProjects = $input->getOption('projects');

        if (!$shouldCompileDependencies && !$shouldCompileProjects) {
            $shouldCompileDependencies = true;
            $shouldCompileProjects = true;
        }

        if ($shouldCompileProjects && $shouldCompileDependencies) {
            $output->writeln('Compiling projects and dependencies...');
        } elseif ($shouldCompileProjects) {
            $output->writeln('Compiling projects...');
        } elseif ($shouldCompileDependencies) {
            $output->writeln('Compiling dependencies...');
        }

        // Before we compile the files we calculate the dependency graph so we know which projects depend on each
        // other. We will than compile bottom up where we first compile the projects that are not depending on other
        // projects, etc.
        $dependencyGraph = new DependencyGraph(
            $this->getConfigLoader(),
            $shouldCompileProjects,
            $shouldCompileDependencies
        );

        // Determine the compiler that we shoule use:
        $compiler = $this->determineCompiler($input);
        if (!$compiler) {
            throw new RuntimeException('Failed to find a compiler, cannot compile the projects.');
        }

        foreach ($dependencyGraph as $packageName => $projectToCompile) {
            $this->compilePackage($output, $compiler, $packageName, $projectToCompile);
        }
    }

    protected function compilePackage(
        OutputInterface $output,
        CompilerInterface $compiler,
        $packageName,
        $projectToCompile
    ) {
        $oldWorkingDirectory = $this->getConfigLoader()->getWorkingDirectory();
        $newWorkingDirectory = $this->getConfigLoader()->getWorkingDirectory($packageName);

        $output->writeln(sprintf(
            '<info>Compiling project "%s" from package "%s"</info>',
            $projectToCompile->getName(),
            $packageName
        ));

        chdir($newWorkingDirectory);

        $compiler->getVariableParser()->push('ide.project', $projectToCompile);

        foreach ($projectToCompile->getConfigurations() as $configuration) {
            $output->writeln(sprintf(
                '<info>Compiling "%s"...</info>',
                $configuration->getName()
            ));

            $compiler->getVariableParser()->push('ide.config', $configuration);

            $compiler->compile($projectToCompile, $configuration);

            $compiler->getVariableParser()->pop('ide.config');
        }

        $compiler->getVariableParser()->pop('ide.project');

        chdir($oldWorkingDirectory);
    }

    /**
     * @param InputInterface $input
     * @return CompilerInterface|null
     */
    protected function determineCompiler(InputInterface $input)
    {
        $compiler = null;
        $variableParser = new Parser();

        $locators = [
            'msvc' => new Msvc($variableParser, $this->getConfigLoader()),
        ];

        $preferredCompilerName = $input->getArgument('compiler');
        if (array_key_exists($preferredCompilerName, $locators)) {
            $preferredCompiler = $locators[$preferredCompilerName];

            $compiler = $preferredCompiler->locate();
        }

        if (!$compiler) {
            foreach ($locators as $locator) {
                $compiler = $locator->locate();
                if ($compiler) {
                    break;
                }
            }
        }

        return $compiler;
    }
}
