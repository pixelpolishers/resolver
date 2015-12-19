<?php

namespace PixelPolishers\Resolver\Generator\VisualStudio\Vs2015;

use PixelPolishers\Resolver\Config\Element\Configuration;
use PixelPolishers\Resolver\Generator\VisualStudio\AbstractSolutionGenerator;

class SolutionGenerator extends AbstractSolutionGenerator
{
    protected function writeHeader()
    {
        $this->write('');
        $this->write('Microsoft Visual Studio Solution File, Format Version 12.00');
        $this->write('# Visual Studio 14');
        $this->write('VisualStudioVersion = 14.0.23107.0');
        $this->write('MinimumVisualStudioVersion = 10.0.40219.1');
    }
}
