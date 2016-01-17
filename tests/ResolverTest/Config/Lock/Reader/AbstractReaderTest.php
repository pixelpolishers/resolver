<?php

namespace PixelPolishers\ResolverTest\Config;

use PHPUnit_Framework_TestCase;
use PixelPolishers\Resolver\Config\Lock\Config;
use PixelPolishers\Resolver\Config\Lock\Reader\Json;

class AbstractReaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers PixelPolishers\Resolver\Config\Lock\Reader\AbstractReader::__construct
     * @covers PixelPolishers\Resolver\Config\Lock\Reader\AbstractReader::read
     * @covers PixelPolishers\Resolver\Config\Lock\Reader\AbstractReader::readFile
     */
    public function testReadWithNonExistingFile()
    {
        // Assert
        $this->setExpectedException('InvalidArgumentException', 'The path "invalid" does not exists.');

        // Arrange
        $reader = new Json();

        // Act
        $reader->read('invalid');
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Lock\Reader\AbstractReader::__construct
     * @covers PixelPolishers\Resolver\Config\Lock\Reader\AbstractReader::read
     * @covers PixelPolishers\Resolver\Config\Lock\Reader\AbstractReader::readFile
     */
    public function testReadWithValidFile()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../../ResolverTestAsset/valid-lock.json');

        // Assert
        $this->assertInstanceOf(Config::class, $result);
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Lock\Reader\AbstractReader::parseData
     * @covers PixelPolishers\Resolver\Config\Lock\Reader\AbstractReader::parseConstraints
     */
    public function testParseConstraints()
    {
        // Arrange
        $reader = new Json();

        // Act
        $constraints = $reader->read(__DIR__ . '/../../../../ResolverTestAsset/valid-lock.json')->getConstraints();

        // Assert
        $this->assertCount(1, $constraints);
    }
}
