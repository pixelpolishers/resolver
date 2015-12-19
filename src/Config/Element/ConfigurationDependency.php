<?php

namespace PixelPolishers\Resolver\Config\Element;

class ConfigurationDependency
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $config;

    public function __construct($name, $config)
    {
        $this->name = $name;
        $this->config = $config;
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
    public function getConfig()
    {
        return $this->config;
    }
}
