<?php

namespace PixelPolishers\Resolver\Package;

use Composer\Semver\Constraint\ConstraintInterface;

class Package implements PackageInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var ConstraintInterface
     */
    private $version;

    /**
     * @var Source
     */
    private $source;

    /**
     * @var boolean
     */
    private $developmentPackage;

    /**
     * @var string[]
     */
    private $dependencies;

    /**
     * @var string[]
     */
    private $developmentDependencies;

    public function __construct()
    {
        $this->dependencies = [];
        $this->developmentDependencies = [];
        $this->developmentPackage = false;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion(ConstraintInterface $version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getPrettyVersion()
    {
        if ($this->getDevelopmentPackage() && in_array($this->getSource()->getType(), ['hq', 'git', 'svn'])) {
            return $this->getSource()->getReference();
        }

        return $this->getVersion()->getPrettyString();
    }

    public function getSource()
    {
        return $this->source;
    }

    public function setSource(Source $source)
    {
        $this->source = $source;
    }

    /**
     * @return boolean
     */
    public function getDevelopmentPackage()
    {
        return $this->developmentPackage;
    }

    /**
     * @param boolean $developmentPackage
     */
    public function setDevelopmentPackage($developmentPackage)
    {
        $this->developmentPackage = $developmentPackage;
    }

    public function getDependencies()
    {
        return $this->dependencies;
    }

    public function setDependencies($dependencies)
    {
        $this->dependencies = $dependencies;
    }

    /**
     * @return \string[]
     */
    public function getDevelopmentDependencies()
    {
        return $this->developmentDependencies;
    }

    /**
     * @param \string[] $developmentDependencies
     */
    public function setDevelopmentDependencies($developmentDependencies)
    {
        $this->developmentDependencies = $developmentDependencies;
    }
}
