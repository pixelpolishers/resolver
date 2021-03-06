<?php

namespace PixelPolishers\Resolver\Config\Reader;

use InvalidArgumentException;
use PixelPolishers\Resolver\Config\Config;
use PixelPolishers\Resolver\Config\Element\Configuration;
use PixelPolishers\Resolver\Config\Element\ConfigurationDependency;
use PixelPolishers\Resolver\Config\Element\Dependency;
use PixelPolishers\Resolver\Config\Element\Paths;
use PixelPolishers\Resolver\Config\Element\PrecompiledHeader;
use PixelPolishers\Resolver\Config\Element\Project;
use PixelPolishers\Resolver\Config\Element\Repository;
use PixelPolishers\Resolver\Config\Element\Source;
use PixelPolishers\Resolver\Config\Type\ProjectType;
use PixelPolishers\Resolver\Config\Type\Subsystem;
use RuntimeException;

abstract class AbstractReader implements ReaderInterface
{
    public function read($path)
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException(sprintf('The path "%s" does not exists.', $path));
        }

        return $this->readFile($path);
    }

    protected function readFile($path)
    {
        $data = $this->parseContent($path);

        $config = new Config();

        $this->parseData($config, $data);

        return $config;
    }

    abstract protected function parseContent($path);

    protected function parseData(Config $config, $data)
    {
        if (!array_key_exists('name', $data)) {
            throw new RuntimeException('The name property is not set.');
        }

        $this->parseName($config, $data);
        $this->parseDefinitions($config, $data);
        $this->parseDescription($config, $data);
        $this->parseLicense($config, $data);
        $this->parseProjectsDirectory($config, $data);
        $this->parseVendorDirectory($config, $data);

        if (array_key_exists('projects', $data)) {
            $projects = $this->parseProjects($data['projects']);

            $config->setProjects($projects);
        }

        if (array_key_exists('repositories', $data)) {
            $repositories = $this->parseRepositories($data['repositories']);

            $config->setRepositories($repositories);
        }
    }

    protected function parseName(Config $config, $data)
    {
        if (strpos($data['name'], '/') === false) {
            throw new RuntimeException('Invalid name, missing vendor.');
        }

        list($vendor, $name) = explode('/', $data['name'], 2);

        $config->setName($name);
        $config->setVendor($vendor);
    }

    protected function parseDefinitions(Config $config, $data)
    {
        if (array_key_exists('definitions', $data)) {
            $config->setDefinitions($data['definitions']);
        }
    }

    protected function parseDescription(Config $config, $data)
    {
        if (array_key_exists('description', $data)) {
            $config->setDescription($data['description']);
        }
    }

    protected function parseLicense(Config $config, $data)
    {
        if (array_key_exists('license', $data)) {
            $config->setLicense($data['license']);
        }
    }

    protected function parseProjectsDirectory(Config $config, $data)
    {
        if (array_key_exists('projects-dir', $data)) {
            $config->setProjectsDirectory($data['projects-dir']);
        }
    }

    protected function parseVendorDirectory(Config $config, $data)
    {
        if (array_key_exists('vendor-dir', $data)) {
            $config->setVendorDirectory($data['vendor-dir']);
        }
    }

    protected function parseProjects($projects)
    {
        $projectList = [];

        foreach ($projects as $data) {
            $project = new Project();

            $this->parseProjectConfigurationsData($project, $data);
            $this->parseProjectDefinitions($project, $data);
            $this->parseProjectDependenciesData($project, $data);
            $this->parseProjectDevelopmentDependenciesData($project, $data);
            $this->parseProjectName($project, $data);
            $this->parseProjectPathsData($project, $data);
            $this->parseProjectPrecompiledHeaderData($project, $data);
            $this->parseProjectSourceData($project, $data);
            $this->parseProjectSubsystem($project, $data);
            $this->parseProjectType($project, $data);

            $projectList[] = $project;
        }

        return $projectList;
    }

    protected function parseProjectDependenciesData(Project $project, $data)
    {
        if (array_key_exists('dependencies', $data)) {
            $dependencies = $this->parseProjectDependencies($data['dependencies']);

            $project->setDependencies($dependencies);
        }
    }

    protected function parseProjectDevelopmentDependenciesData(Project $project, $data)
    {
        if (array_key_exists('dependencies-dev', $data)) {
            $dependencies = $this->parseProjectDependencies($data['dependencies-dev']);

            $project->setDevelopmentDependencies($dependencies);
        }
    }

    protected function parseProjectConfigurationsData(Project $project, $data)
    {
        if (array_key_exists('configurations', $data)) {
            $configurations = $this->parseProjectConfigurations($project, $data['configurations']);

            $project->setConfigurations($configurations);
        }
    }

    protected function parseProjectDefinitions(Project $project, $data)
    {
        if (array_key_exists('definitions', $data)) {
            $project->setDefinitions($data['definitions']);
        }
    }

    protected function parseProjectName(Project $project, $data)
    {
        if (array_key_exists('name', $data)) {
            $project->setName($data['name']);
        }
    }

    protected function parseProjectPathsData(Project $project, $data)
    {
        if (array_key_exists('paths', $data)) {
            $paths = $this->parsePaths($data['paths']);

            $project->setPaths($paths);
        }
    }

    protected function parseProjectPrecompiledHeaderData(Project $project, $data)
    {
        if (array_key_exists('pch', $data)) {
            $pch = $this->parsePrecompiledHeader($data['pch']);

            $project->setPrecompiledHeader($pch);
        }
    }

    protected function parseProjectSourceData(Project $project, $data)
    {
        if (array_key_exists('source', $data)) {
            $source = $this->parseSource($data['source']);

            $project->setSource($source);
        }
    }

    protected function parseProjectSubsystem(Project $project, $data)
    {
        if (array_key_exists('subsystem', $data)) {
            $project->setSubsystem($data['subsystem']);
        } else {
            $project->setSubsystem(Subsystem::CONSOLE);
        }
    }

    protected function parseProjectType(Project $project, $data)
    {
        if (array_key_exists('type', $data)) {
            $project->setType($data['type']);
        } else {
            $project->setType(ProjectType::APPLICATION);
        }
    }

    protected function parseRepositories($data)
    {
        $repositories = [];

        foreach ($data as $element) {
            if (!array_key_exists('type', $element)) {
                continue;
            }

            $repository = new Repository($element['type']);

            unset($element['type']);

            $repository->setParams($element);

            $repositories[] = $repository;
        }

        return $repositories;
    }

    protected function parseProjectConfigurations(Project $project, $configs)
    {
        $configList = [];

        foreach ($configs as $data) {
            $config = new Configuration($project);

            if (array_key_exists('debug', $data)) {
                $config->setDebug($data['debug']);
            }

            if (array_key_exists('definitions', $data)) {
                $config->setDefinitions($data['definitions']);
            }

            if (array_key_exists('dependencies', $data)) {
                $dependencies = $this->parseConfigurationDependencies($data['dependencies']);

                $config->setDependencies($dependencies);
            }

            if (array_key_exists('intermediate-dir', $data)) {
                $config->setIntermediateDirectory($data['intermediate-dir']);
            }

            if (array_key_exists('name', $data)) {
                $config->setName($data['name']);
            }

            if (array_key_exists('output', $data)) {
                $config->setOutputPath($data['output']);
            }

            if (array_key_exists('paths', $data)) {
                $paths = $this->parsePaths($data['paths']);

                $config->setPaths($paths);
            }

            if (array_key_exists('pch', $data)) {
                $pch = $this->parsePrecompiledHeader($data['pch']);

                $config->setPrecompiledHeader($pch);
            }

            if (array_key_exists('platform', $data)) {
                $config->setPlatform($data['platform']);
            }

            if (array_key_exists('warning-level', $data)) {
                $config->setWarningLevel($data['warning-level']);
            }

            $configList[] = $config;
        }

        return $configList;
    }

    protected function parseConfigurationDependencies($data)
    {
        $result = [];

        foreach ($data as $config) {
            if (is_string($config)) {
                $dependency = new ConfigurationDependency($config, null);
            } else {
                $dependency = new ConfigurationDependency($config['name'], $config['config']);
            }

            $result[] = $dependency;
        }

        return $result;
    }

    protected function parseProjectDependencies($data)
    {
        $result = [];

        foreach ($data as $config) {
            if (!array_key_exists('name', $config)) {
                throw new InvalidArgumentException('Missing "name" for dependency.');
            }

            if (!array_key_exists('version', $config)) {
                throw new InvalidArgumentException('Missing "version" for dependency.');
            }

            $dependency =

            $result[] = new Dependency($config['name'], $config['version']);
        }

        return $result;
    }

    protected function parsePaths($data)
    {
        $paths = new Paths();

        if (array_key_exists('exclude', $data)) {
            $paths->setExclude($data['exclude']);
        }

        if (array_key_exists('executable', $data)) {
            $paths->setExecutable($data['executable']);
        }

        if (array_key_exists('include', $data)) {
            $paths->setInclude($data['include']);
        }

        if (array_key_exists('library', $data)) {
            $paths->setLibrary($data['library']);
        }

        if (array_key_exists('reference', $data)) {
            $paths->setReference($data['reference']);
        }

        if (array_key_exists('source', $data)) {
            $paths->setSource($data['source']);
        }

        return $paths;
    }

    protected function parsePrecompiledHeader($data)
    {
        $precompiledHeader = new PrecompiledHeader();

        if (array_key_exists('header', $data)) {
            $precompiledHeader->setHeader($data['header']);
        }

        if (array_key_exists('memory', $data)) {
            $precompiledHeader->setMemory($data['memory']);
        }

        if (array_key_exists('name', $data)) {
            $precompiledHeader->setName($data['name']);
        }

        if (array_key_exists('source', $data)) {
            $precompiledHeader->setSource($data['source']);
        }

        return $precompiledHeader;
    }

    protected function parseSource($data)
    {
        $source = new Source();

        if (array_key_exists('extensions', $data)) {
            $source->setExtensions($data['extensions']);
        }

        if (array_key_exists('name', $data)) {
            $source->setName($data['name']);
        }

        if (array_key_exists('paths', $data)) {
            $source->setPaths($data['paths']);
        }

        if (array_key_exists('sources', $data)) {
            if (is_bool($data['sources'])) {
                $source->setExpand($data['sources']);
            } else {
                $sources = [];

                foreach ($data['sources'] as $sourceData) {
                    $sources[] = $this->parseSource($sourceData);
                }

                $source->setSources($sources);
            }
        }

        return $source;
    }
}
