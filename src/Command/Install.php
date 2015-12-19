<?php

namespace PixelPolishers\Resolver\Command;

use Exception;
use PixelPolishers\Resolver\Config\ConfigInterface;
use PixelPolishers\Resolver\Config\Element\Repository;
use PixelPolishers\Resolver\Config\Lock\ConfigInterface as LockConfigInterface;
use PixelPolishers\Resolver\Dependency\DependencyResolver;
use PixelPolishers\Resolver\Repository\Manager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Install extends Update
{
    protected function configure()
    {
        $this->setName('install');
        $this->setDescription('Installs the project with the configuration from the lock file.');
        $this->addOption('no-dev', null, InputOption::VALUE_NONE, 'Disables installation of require-dev packages.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $config ConfigInterface */
        $config = $this->getConfig();

        try {
            /** @var $lockConfig LockConfigInterface */
            $lockConfig = $this->getLockConfig();
        } catch (Exception $e) {
            $lockConfig = null;
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }

        /** @var bool $requiresDevelopmentPackages */
        $requiresDevelopmentPackages = !$input->getOption('no-dev');

        $manager = $this->loadRepositories($output, $config->getRepositories());

        try {
            $output->writeln('Resolving dependencies...');
            if ($lockConfig) {
                $resolver = $this->resolveLockDependencies($manager, $lockConfig, $requiresDevelopmentPackages);
            } else {
                $resolver = $this->resolveDependencies($manager, $config, $requiresDevelopmentPackages);
            }
        } catch (Exception $e) {
            $output->writeln('');
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            $output->writeln('');
            return 1;
        }

        if (!$resolver->hasResolvedDependencies()) {
            $output->writeln('<info>No resolved dependencies found.</info>');
            return 0;
        }

        $this->downloadPackages($output, $resolver);

        if (!$lockConfig) {
            $this->saveLockFile($output, $resolver);
        }

        return 0;
    }

    protected function resolveLockDependencies(
        Manager $manager,
        LockConfigInterface $config,
        $requiresDevelopmentPackages
    ) {
        $resolver = new DependencyResolver($manager);

        foreach ($config->getConstraints() as $constraint) {
            if ($constraint->getDevelopmentPackage() && !$requiresDevelopmentPackages) {
                continue;
            }

            $resolver->resolve(
                $constraint->getName(),
                $constraint->getVersion(),
                $constraint->getDevelopmentPackage()
            );
        }

        return $resolver;
    }
}
