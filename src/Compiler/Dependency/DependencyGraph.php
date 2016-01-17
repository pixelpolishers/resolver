<?php

namespace PixelPolishers\Resolver\Compiler\Dependency;

use ArrayObject;
use DirectoryIterator;
use IteratorAggregate;
use PixelPolishers\Resolver\Config\ConfigInterface;
use PixelPolishers\Resolver\Config\Element\Configuration;
use PixelPolishers\Resolver\Config\Element\Project;
use PixelPolishers\Resolver\Config\Element\Source;
use PixelPolishers\Resolver\Config\Loader;
use RuntimeException;
use Traversable;

class DependencyGraph implements IteratorAggregate
{
    /**
     * @var Loader
     */
    private $loader;

    /**
     * @var boolean
     */
    private $shouldCompileProjects;

    /**
     * @var boolean
     */
    private $shouldCompileDependencies;

    /**
     * @var string[]
     */
    private $builtConfigs;

    /**
     * @var int
     */
    private $buildDepth;

    public function __construct(Loader $loader, $shouldCompileProjects, $shouldCompileDependencies)
    {
        $this->loader = $loader;
        $this->shouldCompileProjects = $shouldCompileProjects;
        $this->shouldCompileDependencies = $shouldCompileDependencies;
        $this->builtConfigs = [];
        $this->buildDepth = 0;
    }

    public function getIterator()
    {
        $result = new ArrayObject();

        $this->buildFileListForConfig($result, $this->loader->getConfig());

        return $result;
    }

    private function buildFileListForConfig(ArrayObject $result, ConfigInterface $config)
    {
        $name = $config->getVendor() . '/' . $config->getName();

        if (in_array($name, $this->builtConfigs)) {
            return;
        }

        $this->builtConfigs[] = $name;

        foreach ($config->getProjects() as $project) {
            $this->buildFileListFromProject($result, $config, $project);
        }
    }

    private function buildFileListFromProject(ArrayObject $result, ConfigInterface $config, Project $project)
    {
        if ($this->shouldCompileDependencies) {
            foreach ($project->getConfigurations() as $configuration) {
                $this->buildFileListFromConfiguration($result, $configuration);
            }
        }

        if (!$this->shouldCompileProjects && $this->buildDepth === 0) {
            return;
        }

        $result[$config->getVendor() . '/' . $config->getName()] = $project;
    }

    private function buildFileListFromConfiguration(ArrayObject $result, Configuration $configuration)
    {
        $this->buildDepth++;
        foreach ($configuration->getDependencies() as $dependency) {
            $config = $this->loader->getConfig($dependency->getName());

            $oldWorkingDirectory = getcwd();
            $newWorkingDirectory = $this->loader->getWorkingDirectory($dependency->getName());
            chdir($newWorkingDirectory);

            $this->buildFileListForConfig($result, $config);

            chdir($oldWorkingDirectory);
        }
        $this->buildDepth--;
    }
}
