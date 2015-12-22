<?php

namespace PixelPolishers\Resolver\Config;

use PixelPolishers\Resolver\Config\Element\Project;
use PixelPolishers\Resolver\Config\Element\Repository;
use PixelPolishers\Resolver\Config\Element\Traits\DefinitionsTrait;

class Config implements ConfigInterface
{
    use DefinitionsTrait;

    /**
     * @var string
     */
    private $description;

    /**
     * @var boolean
     */
    private $hideSolutionNode;

    /**
     * @var string
     */
    private $license;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Project[]
     */
    private $projects;

    /**
     * @var Repository[]
     */
    private $repositories;

    /**
     * @var string
     */
    private $vendor;

    /**
     * @var string
     */
    private $vendorDirectory;

    /**
     * @var string
     */
    private $projectsDirectory;

    public function __construct()
    {
        $this->vendorDirectory = 'vendor';
        $this->projectsDirectory = 'projects';
        $this->projects = [];
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return boolean
     */
    public function getHideSolutionNode()
    {
        return $this->hideSolutionNode;
    }

    /**
     * @param boolean $hideSolutionNode
     */
    public function setHideSolutionNode($hideSolutionNode)
    {
        $this->hideSolutionNode = $hideSolutionNode === true || $hideSolutionNode === 'true';
    }

    /**
     * @return string
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * @param string $license
     */
    public function setLicense($license)
    {
        $this->license = $license;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return Project[]
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * @param Project[] $projects
     */
    public function setProjects($projects)
    {
        $this->projects = $projects;
    }

    public function findProject($name)
    {
        foreach ($this->getProjects() as $project) {
            if ($project->getName() === $name) {
                return $project;
            }
        }

        return null;
    }

    /**
     * @return Repository[]
     */
    public function getRepositories()
    {
        return $this->repositories;
    }

    /**
     * @param Repository[] $repositories
     */
    public function setRepositories($repositories)
    {
        $this->repositories = $repositories;
    }

    /**
     * @return string
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * @param string $vendor
     */
    public function setVendor($vendor)
    {
        $this->vendor = $vendor;
    }

    /**
     * @return string
     */
    public function getVendorDirectory()
    {
        return $this->vendorDirectory;
    }

    /**
     * @param string $vendorDirectory
     */
    public function setVendorDirectory($vendorDirectory)
    {
        $this->vendorDirectory = trim($vendorDirectory, '/');
    }

    /**
     * @return string
     */
    public function getProjectsDirectory()
    {
        return $this->projectsDirectory;
    }

    /**
     * @param string $projectsDirectory
     */
    public function setProjectsDirectory($projectsDirectory)
    {
        $this->projectsDirectory = trim($projectsDirectory, '/');
    }
}
