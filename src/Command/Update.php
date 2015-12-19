<?php

namespace PixelPolishers\Resolver\Command;

use Exception;
use PixelPolishers\Resolver\Config\ConfigInterface;
use PixelPolishers\Resolver\Config\Element\Repository;
use PixelPolishers\Resolver\Dependency\DependencyResolver;
use PixelPolishers\Resolver\Package\PackageInterface;
use PixelPolishers\Resolver\Repository\Manager;
use PixelPolishers\Resolver\Repository\Package;
use PixelPolishers\Resolver\Source\Downloader;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Update extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('update');
        $this->setDescription('Updates the project with the latest configuration.');
        $this->addOption('no-dev', null, InputOption::VALUE_NONE, 'Disables installation of require-dev packages.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $config ConfigInterface */
        $config = $this->getConfig();

        /** @var bool $requiresDevelopmentPackages */
        $requiresDevelopmentPackages = !$input->getOption('no-dev');

        $manager = $this->loadRepositories($output, $config->getRepositories());

        try {
            $output->writeln('Resolving dependencies...');
            $resolver = $this->resolveDependencies($manager, $config, $requiresDevelopmentPackages);
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

        $this->downloadPackages($output, $resolver, $config);

        $this->saveLockFile($output, $resolver);

        return 0;
    }

    protected function downloadPackages(OutputInterface $output, DependencyResolver $resolver, ConfigInterface $config)
    {
        $downloader = new Downloader($config->getVendorDirectory());

        /** @var PackageInterface $package */
        foreach ($resolver->getResolvedDependencies() as $package) {
            try {
                $output->writeln(sprintf(
                    '<info>Downloading %s (%s)</info>',
                    $package->getName(),
                    $package->getPrettyVersion()
                ));

                $downloader->downloadPackage($output, $package);
            } catch (Exception $e) {
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            }
        }
    }

    protected function loadRepositories(OutputInterface $output, array $repositories)
    {
        $output->writeln('Loading resolver repositories with package information...');

        $manager = new Manager();

        $defaultRepository = new Repository('package');
        $defaultRepository->setParams(['url' => 'https://resolver.pixelpolishers.com/packages.json']);
        $repositories[] = $defaultRepository;

        foreach ($repositories as $repositoryConfig) {
            switch ($repositoryConfig->getType()) {
                case 'package':
                    $output->writeln(sprintf(
                        '<info>Loading package repository %s</info>',
                        $repositoryConfig->getParam('url')
                    ));

                    $repository = new Package($repositoryConfig->getParam('url'));
                    break;

                default:
                    throw new RuntimeException('Invalid repository type provided: ' . $repositoryConfig->getType());
            }

            $manager->addRepository($repository);
        }

        $output->writeln('');

        return $manager;
    }

    protected function resolveDependencies(Manager $manager, ConfigInterface $config, $requiresDevelopmentPackages)
    {
        $resolver = new DependencyResolver($manager);

        foreach ($config->getProjects() as $project) {
            foreach ($project->getDependencies() as $dependency) {
                $resolver->resolve($dependency->getName(), $dependency->getVersion(), false);
            }

            if (!$requiresDevelopmentPackages) {
                continue;
            }

            foreach ($project->getDevelopmentDependencies() as $dependency) {
                $resolver->resolve($dependency->getName(), $dependency->getVersion(), true);
            }
        }

        return $resolver;
    }

    protected function saveLockFile(OutputInterface $output, DependencyResolver $resolver)
    {
        $output->writeln('');
        $output->writeln('Writing lock file...');

        $data = [
            'constraints' => [],
        ];

        /** @var PackageInterface $package */
        foreach ($resolver->getResolvedDependencies() as $package) {
            $output->writeln(sprintf(
                '<info>%s (%s)</info>',
                $package->getName(),
                $package->getVersion()->getPrettyString()
            ));

            $data['constraints'][$package->getName()] = [
                'development' => $package->getVersion()->getPrettyString(),
                'version' => $package->getVersion()->getPrettyString(),
                'source' => [
                    'type' => $package->getSource()->getType(),
                    'url' => $package->getSource()->getUrl(),
                    'reference' => $package->getSource()->getReference(),
                ],
            ];
        }

        file_put_contents('resolver.lock', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    }
}
