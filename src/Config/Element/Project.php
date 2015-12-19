<?php

namespace PixelPolishers\Resolver\Config\Element;

use PixelPolishers\Resolver\Config\Element\Traits\DefinitionsTrait;
use PixelPolishers\Resolver\Config\Element\Traits\PathsTrait;
use PixelPolishers\Resolver\Config\Element\Traits\PrecompiledHeaderTrait;

class Project
{
    use DefinitionsTrait;
    use PrecompiledHeaderTrait;
    use PathsTrait;

    /**
     * @var Configuration[]
     */
    private $configurations;

    /**
     * @var Dependency[]
     */
    private $dependencies;

    /**
     * @var Dependency[]
     */
    private $developmentDependencies;

    /*
     * @var string
     */
    private $name;

    /*
     * @var Source
     */
    private $source;

    /*
     * @var string
     */
    private $subsystem;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $uuid;

    public function __construct()
    {
        $this->configurations = [];
        $this->dependencies = [];
        $this->developmentDependencies = [];
    }

    /**
     * @return Configuration[]
     */
    public function getConfigurations()
    {
        return $this->configurations;
    }

    /**
     * @param Configuration[] $configurations
     */
    public function setConfigurations($configurations)
    {
        $this->configurations = $configurations;
    }

    /**
     * @return Dependency[]
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * @param Dependency[] $dependencies
     */
    public function setDependencies($dependencies)
    {
        $this->dependencies = $dependencies;
    }

    /**
     * @return Dependency[]
     */
    public function getDevelopmentDependencies()
    {
        return $this->developmentDependencies;
    }

    /**
     * @param Dependency[] $developmentDependencies
     */
    public function setDevelopmentDependencies($developmentDependencies)
    {
        $this->developmentDependencies = $developmentDependencies;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return Source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param Source $source
     */
    public function setSource(Source $source)
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getSubsystem()
    {
        return $this->subsystem;
    }

    /**
     * @param mixed $subsystem
     */
    public function setSubsystem($subsystem)
    {
        $this->subsystem = $subsystem;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }
}
