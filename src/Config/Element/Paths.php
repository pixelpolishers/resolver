<?php

namespace PixelPolishers\Resolver\Config\Element;

class Paths
{
    /**
     * @var string[]
     */
    private $exclude;

    /**
     * @var string[]
     */
    private $executable;

    /**
     * @var string[]
     */
    private $include;

    /**
     * @var string[]
     */
    private $library;

    /**
     * @var string[]
     */
    private $reference;

    /**
     * @var string[]
     */
    private $source;

    /**
     * @return \string[]
     */
    public function getExclude()
    {
        return $this->exclude;
    }

    /**
     * @param \string[] $exclude
     */
    public function setExclude($exclude)
    {
        $this->exclude = $exclude;
    }

    /**
     * @return \string[]
     */
    public function getExecutable()
    {
        return $this->executable;
    }

    /**
     * @param \string[] $executable
     */
    public function setExecutable($executable)
    {
        $this->executable = $executable;
    }

    /**
     * @return \string[]
     */
    public function getInclude()
    {
        return $this->include;
    }

    /**
     * @param \string[] $include
     */
    public function setInclude($include)
    {
        $this->include = $include;
    }

    /**
     * @return \string[]
     */
    public function getLibrary()
    {
        return $this->library;
    }

    /**
     * @param \string[] $library
     */
    public function setLibrary($library)
    {
        $this->library = $library;
    }

    /**
     * @return \string[]
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param \string[] $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    /**
     * @return \string[]
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param \string[] $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }
}
