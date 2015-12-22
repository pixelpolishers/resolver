<?php

namespace PixelPolishers\ResolverTest\Config;

use PHPUnit_Framework_TestCase;
use PixelPolishers\Resolver\Config\Element\Repository;
use PixelPolishers\Resolver\Config\Config;
use PixelPolishers\Resolver\Config\Reader\Json;

class AbstractReaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::read
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseData
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
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::read
     */
    public function testReadWithValidFile()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/full.json');

        // Assert
        $this->assertInstanceOf(Config::class, $result);
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseData
     */
    public function testParseDataWithMissingVendor()
    {
        // Assert
        $this->setExpectedException('RuntimeException', 'Invalid name, missing vendor.');

        // Arrange
        $reader = new Json();

        // Act
        $reader->read(__DIR__ . '/../../../ResolverTestAsset/missing-vendor-in-name.json');
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseData
     */
    public function testParseDataWithMissingName()
    {
        // Assert
        $this->setExpectedException('RuntimeException', 'The name property is not set.');

        // Arrange
        $reader = new Json();

        // Act
        $reader->read(__DIR__ . '/../../../ResolverTestAsset/missing-name.json');
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseData
     */
    public function testParseDataName()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/full.json');

        // Assert
        $this->assertEquals('name', $result->getName());
        $this->assertEquals('vendor', $result->getVendor());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseData
     */
    public function testParseDataDefinitions()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/full.json');

        // Assert
        $this->assertEquals(['test1', 'test2'], $result->getDefinitions());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseData
     */
    public function testParseDataDescription()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/full.json');

        // Assert
        $this->assertEquals('description', $result->getDescription());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseData
     */
    public function testParseDataLicense()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/full.json');

        // Assert
        $this->assertEquals('MIT', $result->getLicense());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseData
     */
    public function testParseDataProjectsDir()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/full.json');

        // Assert
        $this->assertEquals('my-projects', $result->getProjectsDirectory());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseData
     */
    public function testParseDataProjects()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/full.json');

        // Assert
        $this->assertCount(2, $result->getProjects());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseData
     */
    public function testParseDataRepositories()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/full.json');

        // Assert
        $this->assertCount(2, $result->getRepositories());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseData
     */
    public function testParseDataVendorDir()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/full.json');

        // Assert
        $this->assertEquals('my-vendor', $result->getVendorDirectory());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseRepositories
     */
    public function testparseRepositories()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/full.json');
        $repositories = $result->getRepositories();
        $repositoriy = $repositories[0];

        // Assert
        $this->assertInstanceOf(Repository::class, $repositoriy);
        $this->assertEquals('repository', $repositoriy->getType());
        $this->assertEquals(['param1' => 'value1', 'param2' => 'value2'], $repositoriy->getParams());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseRepositories
     */
    public function testparseRepositoriesWithMissingType()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/missing-repository-type.json');

        // Assert
        $this->assertCount(0, $result->getRepositories());
    }
}
