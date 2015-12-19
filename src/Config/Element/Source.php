<?php

namespace PixelPolishers\Resolver\Config\Element;

class Source
{
    /**
     * @var bool
     */
    private $expand;

    /**
     * @var string[]
     */
    private $extensions;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $paths;

    /**
     * @var Source[]
     */
    private $sources;

    public function __construct()
    {
        $this->expand = false;
        $this->extensions = [];
        $this->paths = [];
        $this->sources = [];
    }

    /**
     * @return bool
     */
    public function getExpand()
    {
        return $this->expand;
    }

    /**
     * @param bool $expand
     */
    public function setExpand($expand)
    {
        $this->expand = $expand;
    }

    /**
     * @return \string[]
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * @param \string[] $extensions
     */
    public function setExtensions($extensions)
    {
        $this->extensions = $extensions;
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
     * @return \string[]
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * @param \string[] $paths
     */
    public function setPaths($paths)
    {
        $this->paths = $paths;
    }

    /**
     * @return Source[]
     */
    public function getSources()
    {
        return $this->sources;
    }

    /**
     * @param Source[] $sources
     */
    public function setSources($sources)
    {
        $this->sources = $sources;
    }
}
