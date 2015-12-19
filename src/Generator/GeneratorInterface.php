<?php

namespace PixelPolishers\Resolver\Generator;

use PixelPolishers\Resolver\Config\ConfigInterface;
use PixelPolishers\Resolver\Variable\Parser;

interface GeneratorInterface
{
    /**
     * @return ConfigInterface
     */
    public function getConfig();

    /**
     * @return Parser
     */
    public function getVariableParser();

    /**
     * @param string $targetDirectory
     * @return void
     */
    public function generate($targetDirectory);
}
