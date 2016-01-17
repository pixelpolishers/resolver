<?php

namespace PixelPolishers\Resolver\Compiler;

use PixelPolishers\Resolver\Config\Element\Configuration;
use PixelPolishers\Resolver\Config\Element\Project;
use PixelPolishers\Resolver\Variable\Parser;

interface CompilerInterface
{
    /**
     * @return Parser
     */
    public function getVariableParser();

    /**
     * @param Project $project The project to compile.
     * @param Configuration $configuration The project configuration to compile.
     */
    public function compile(Project $project, Configuration $configuration);
}
