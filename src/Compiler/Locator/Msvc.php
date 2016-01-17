<?php

namespace PixelPolishers\Resolver\Compiler\Locator;

use PixelPolishers\Resolver\Compiler\Msvc as MsvcCompiler;
use PixelPolishers\Resolver\Config\Loader;
use PixelPolishers\Resolver\Variable\Parser;

class Msvc implements LocatorInterface
{
    private $variableParser;
    private $configLoader;

    public function __construct(Parser $variableParser, Loader $configLoader)
    {
        $this->variableParser = $variableParser;
        $this->configLoader = $configLoader;
    }

    public function locate()
    {
        $paths = [
            'C:\\Program Files (x86)\\Microsoft Visual Studio 14.0\\VC',
            'C:\\Program Files (x86)\\Microsoft Visual Studio 13.0\\VC',
            'C:\\Program Files (x86)\\Microsoft Visual Studio 12.0\\VC',
            'C:\\Program Files (x86)\\Microsoft Visual Studio 11.0\\VC',
        ];

        foreach ($paths as $name => $path) {
            if (is_dir($path)) {
                return new MsvcCompiler($path, $this->variableParser, $this->configLoader);
            }
        }

        return null;
    }
}
