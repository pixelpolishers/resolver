<?php

namespace PixelPolishers\Resolver\Generator\VisualStudio;

use PixelPolishers\Resolver\Config\ConfigInterface;
use PixelPolishers\Resolver\Config\Element\Configuration;
use PixelPolishers\Resolver\Config\Element\Project;
use PixelPolishers\Resolver\Config\Type\Platform;
use PixelPolishers\Resolver\Generator\VisualStudio\Traits\PlatformTrait;
use RuntimeException;

abstract class AbstractSolutionGenerator
{
    use PlatformTrait;

    /**
     * @var resource
     */
    private $handle;

    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct($path, ConfigInterface $config)
    {
        $this->handle = fopen($path, 'wb');
        if (!$this->handle) {
            throw new RuntimeException(sprintf('Failed to open stream to %s', $path));
        }

        $this->config = $config;
    }

    public function __destruct()
    {
        if ($this->handle) {
            fclose($this->handle);
        }
    }

    /**
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function generate()
    {
        $this->writeBom();
        $this->writeHeader();
        $this->writeProjectList();
        $this->writeGlobal();
    }

    protected abstract function writeHeader();

    protected function writeBom()
    {
        fwrite($this->handle, chr(239) . chr(187) . chr(191));
    }

    protected function writeProjectList()
    {
        foreach ($this->config->getProjects() as $project) {
            $this->writeProject($project);
        }
    }

    protected function writeProject(Project $project)
    {
        $projectGuid = '8BC9CEB8-8B4A-11D0-8D11-00A0C91BC942';

        $this->write('Project("{' . $projectGuid . '}") = "'
            . $project->getName() . '", "' . $project->getName()
            . '.vcxproj", "{' . $project->getUuid() . '}"');

        // If this project has dependencies on local projects, let's add them:
        $dependencies = $this->getDependencies($project);
        if (count($dependencies) > 0) {
            $this->write('ProjectSection(ProjectDependencies) = postProject');
            foreach ($dependencies as $dependency) {
                $uuid = $dependency->getUuid();

                $this->write('{' . $uuid . '} = {' . $uuid . '}');
            }
            $this->write('EndProjectSection');
        }

        $this->write('EndProject');
    }

    protected function writeGlobal()
    {
        $this->write('Global');
        $this->writeSolutionConfigurationPlatforms();
        $this->writeProjectConfigurationPlatforms();
        $this->writeSolutionProperties();
        $this->write('EndGlobal');
    }

    protected function writeSolutionConfigurationPlatforms()
    {
        $this->write('GlobalSection(SolutionConfigurationPlatforms) = preSolution', 1);

        foreach ($this->getConfig()->getProjects() as $project) {
            $this->writeSolutionPlatformConfiguration($project);
        }

        $this->write('EndGlobalSection', 1);
    }

    protected function writeSolutionPlatformConfiguration(Project $project)
    {
        foreach ($project->getConfigurations() as $configuration) {
            $this->writeSolutionConfigurationPlatform($configuration);
        }
    }

    protected function writeSolutionConfigurationPlatform(Configuration $configuration)
    {
        $platform = $configuration->getPlatform() ?: 'win32';

        $typeStr = $configuration->getName() . '|' . $this->convertPlatform($platform);

        $this->write($typeStr . ' = ' . $typeStr, 2);
    }

    protected function writeProjectConfigurationPlatforms()
    {
        $this->write('GlobalSection(ProjectConfigurationPlatforms) = postSolution', 1);

        foreach ($this->getConfig()->getProjects() as $project) {
            $this->writeProjectConfiguration($project);
        }

        $this->write('EndGlobalSection', 1);
    }

    protected function writeProjectConfiguration(Project $project)
    {
        foreach ($project->getConfigurations() as $configuration) {
            $this->writeProjectConfigurationPlatform($configuration);
        }
    }

    protected function writeProjectConfigurationPlatform(Configuration $configuration)
    {
        foreach ($this->getConfig()->getProjects() as $project) {
            $typeStr = sprintf(
                '%s|%s',
                $configuration->getName(),
                $this->convertPlatform($configuration->getPlatform())
            );

            $projStr = sprintf('{%s}.%s', $project->getUuid(), $typeStr);

            $this->write(sprintf('%s.ActiveCfg = %s', $projStr, $typeStr), 2);
            $this->write(sprintf('%s.Build.0 = %s', $projStr, $typeStr), 2);
        }
    }

    protected function writeSolutionProperties()
    {
        $this->write('GlobalSection(SolutionProperties) = preSolution', 1);

        if ($this->getConfig()->getHideSolutionNode()) {
            $this->write('HideSolutionNode = TRUE', 2);
        } else {
            $this->write('HideSolutionNode = FALSE', 2);
        }

        $this->write('EndGlobalSection', 1);
    }

    protected function write($line, $depth = 0)
    {
        fwrite($this->handle, str_repeat('    ', $depth) . $line . "\r\n");
    }

    protected function getDependencies(Project $project)
    {
        $result = [];

        foreach ($project->getConfigurations() as $configuration) {
            foreach ($configuration->getDependencies() as $dependency) {
                $dependentProject = $this->config->findProject($dependency->getName());

                if ($dependentProject && !in_array($dependentProject, $result)) {
                    $result[] = $dependentProject;
                }
            }
        }

        return $result;
    }
}
