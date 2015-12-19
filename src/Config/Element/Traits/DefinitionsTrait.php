<?php

namespace PixelPolishers\Resolver\Config\Element\Traits;

trait DefinitionsTrait
{
    /*
     * @var string[]
     */
    private $definitions;

    /**
     * @return string[]
     */
    public function getDefinitions()
    {
        return (array)$this->definitions;
    }

    /**
     * @param string[] $definitions
     */
    public function setDefinitions(array $definitions)
    {
        $this->definitions = $definitions;
    }
}
