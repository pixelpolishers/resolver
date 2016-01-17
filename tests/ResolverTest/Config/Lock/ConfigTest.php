<?php

namespace PixelPolishers\ResolverTest\Config\Lock;

use PHPUnit_Framework_TestCase;
use PixelPolishers\Resolver\Config\Lock\Config;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers PixelPolishers\Resolver\Config\Lock\Config::getConstraints
     * @covers PixelPolishers\Resolver\Config\Lock\Config::setConstraints
     */
    public function testConstraintsGetterSetter()
    {
        // Arrange
        $config = new Config();

        // Act
        $config->setConstraints([1, 2, 3]);

        // Assert
        $this->assertInternalType('array', $config->getConstraints());
        $this->assertEquals([1, 2, 3], $config->getConstraints());
    }
}
