<?php

namespace PixelPolishers\ResolverTest\Config\Lock\Reader;

use PHPUnit_Framework_TestCase;
use PixelPolishers\ResolverTestAsset\Config\Lock\Reader\DummyJson;

class JsonTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers PixelPolishers\Resolver\Config\Lock\Reader\Json::parseContent
     */
    public function testParseContentWithValidJson()
    {
        // Arrange
        $reader = new DummyJson();

        // Act
        $result = $reader->read(__DIR__ . '/../../../../ResolverTestAsset/valid-lock.json');

        // Assert
        $this->assertInternalType('array', $result);
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Lock\Reader\Json::parseContent
     */
    public function testParseContentWithEmptyJson()
    {
        // Arrange
        $reader = new DummyJson();

        // Act
        $result = $reader->read(__DIR__ . '/../../../../ResolverTestAsset/empty-lock.json');

        // Assert
        $this->assertNull($result);
    }
}
