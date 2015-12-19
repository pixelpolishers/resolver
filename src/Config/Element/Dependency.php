<?php

namespace PixelPolishers\Resolver\Config\Element;

class Dependency
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $version;

    public function __construct($name, $version)
    {
        $this->name = $name;
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }
}
