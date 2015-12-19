<?php

namespace PixelPolishers\Resolver\Generator\VisualStudio\Vs2010;

use PixelPolishers\Resolver\Generator\VisualStudio\AbstractSolutionGenerator;

class SolutionGenerator extends AbstractSolutionGenerator
{
    protected function writeHeader()
    {
        $this->write('Microsoft Visual Studio Solution File, Format Version 11.00');
        $this->write('# Visual Studio 2010');
    }
}
