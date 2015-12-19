<?php

namespace PixelPolishers\ResolverTest\Config;

use PHPUnit_Framework_TestCase;
use PixelPolishers\Resolver\Config\Config;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers PixelPolishers\Resolver\Config\Config::__construct
     */
    public function testConstructorInitializesVendorDirectory()
    {
        // Arrange
        // ...

        // Act
        $config = new Config();

        // Assert
        $this->assertEquals('vendor', $config->getVendorDirectory());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Config::__construct
     */
    public function testConstructorInitializesProjectsDirectory()
    {
        // Arrange
        // ...

        // Act
        $config = new Config();

        // Assert
        $this->assertEquals('projects', $config->getProjectsDirectory());
    }
}
