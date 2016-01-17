<?php

namespace PixelPolishers\ResolverTest\Config\Reader;

use PHPUnit_Framework_TestCase;
use PixelPolishers\ResolverTestAsset\Config\Reader\DummyJson;

class JsonTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers PixelPolishers\Resolver\Config\Reader\Json::parseContent
     */
    public function testParseContentWithValidJson()
    {
        // Arrange
        $reader = new DummyJson();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/full.json');

        // Assert
        $this->assertInternalType('array', $result);
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\Json::parseContent
     */
    public function testParseContentWithEmptyJson()
    {
        // Arrange
        $reader = new DummyJson();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/empty.json');

        // Assert
        $this->assertNull($result);
    }
}
