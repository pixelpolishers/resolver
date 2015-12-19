<?php

namespace PixelPolishers\Resolver\Generator;

use PixelPolishers\Resolver\Config\ConfigInterface;
use PixelPolishers\Resolver\Variable\Parser;

abstract class AbstractGenerator implements GeneratorInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var Parser
     */
    private $variableParser;

    /**
     * Initializes a new instance of this class.
     *
     * @param ConfigInterface $config The configuration to generate project files for.
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return Parser
     */
    public function getVariableParser()
    {
        return $this->variableParser;
    }

    /**
     * @param Parser $variableParser
     */
    public function setVariableParser($variableParser)
    {
        $this->variableParser = $variableParser;
    }
}
